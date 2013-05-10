#!/bin/bash

function get_port_num() {
    sql="select port_num from instance where instance_id=${1}"
    ${cmd_mysql} -e "${sql}"
}

function get_solr_version(){
     sql="select s.solr_version from service_mapping m
         left join service s on s.service_id=m.service_id 
         where m.instance_id=${1}"
     ${cmd_mysql} -e "${sql}"
}

function build_instance_root() {
    echo "${1}/cloud/${2}"
}

function get_solr_pid() {
    pgrep -f "cloud/${1}"
}

function get_nginx_pid {
    PID_FILE=$(awk '/pid/{print $2}' "${solr_root}/nginx/nginx.conf" | sed 's/;//')
    if [ -f ${PID_FILE} ]
    then
        cat ${PID_FILE}
    else
        pgrep -f "nginx: master process"
    fi
}

function get_instance_queue() {
    sql="select a.queue_id, a.action_id, a.target_id, b.script_name, a.session_id
         from action_queue a
         left join action b on a.action_id = b.action_id
         left join instance c on a.target_id = c.instance_id
         left join host d on c.host_id = d.host_id
         where a.queue_status=0 and d.host_id=${1} and b.action_type='instance'
         order by queue_id
         limit 1"
    ${cmd_mysql} -e "${sql}"
}

function get_instance_queue_pending() {
    sql="select queue_id from action_queue a
         left join action b on a.action_id = b.action_id
         where queue_status=0 and queue_id<${1}
         and action_type='instance' limit 1"
    ${cmd_mysql} -e "${sql}"
}

function get_service2_queue() {
    sql="select a.queue_id, a.action_id, a.target_id, b.script_name, a.session_id
         from action_queue a
         left join action b on a.action_id = b.action_id
         where a.host_id=${1} and a.queue_status=0 and b.action_type='service2'
         order by queue_id
         limit 1"
    ${cmd_mysql} -e "${sql}"
}

function get_service_queue() {
    sql="select a.queue_id, a.action_id, a.target_id, b.script_name, a.session_id
         from action_queue a
         left join action b on a.action_id = b.action_id
         where a.host_id=${1} and a.queue_status=0 and b.action_type='service'
         order by queue_id
         limit 1"
    ${cmd_mysql} -e "${sql}"
}

function get_service_queue_pending() {
    sql="select queue_id from action_queue a
         left join action b on a.action_id = b.action_id
         where queue_status=0 and queue_id<${1}
             and action_type='service' limit 1"
    ${cmd_mysql} -e "${sql}"
}

function get_action() {
    sql="select script_name, action_type, action_name from action where action_id=${1}"
    ${cmd_mysql} -e "${sql}"
}

function get_host_ip() {
    sql="select b.host_ip from instance a left join host b on a.host_id = b.host_id where instance_id=${1}"
    ${cmd_mysql} -e "${sql}"
}

function get_use_memory() {
    sql="select use_memory from instance where instance_id=${1}"
    ${cmd_mysql} -e "${sql}"
}

function set_queue_succ() {
    sql="update action_queue set queue_status=1 where queue_id=${1}"
    ${cmd_mysql} -e "${sql}"
}

function set_queues_skip() {
    sql="update action_queue set queue_status=3
         where queue_status=0 and action_id=${1} and target_id=${2} and session_id='${3}'"
    ${cmd_mysql} -e "${sql}"
}

function set_queue_fail() {
    sql="update action_queue set queue_status=2 where queue_id=${1}"
    ${cmd_mysql} -e "${sql}"
}

function get_service_status() {
    sql="select service_status from service where service_id=${1}"
    ${cmd_mysql} -e "${sql}"
}

function set_service_unlock() {
    sql="update service set is_locked=0 where service_id=${1}"
    ${cmd_mysql} -e "${sql}"
}

function set_instance_unlock() {
    sql="update instance set is_locked=0 where instance_id=${1}"
    ${cmd_mysql} -e "${sql}"
}

function set_instance_status() {
    sql="update instance set instance_status=${2} where instance_id=${1}"
    ${cmd_mysql} -e "${sql}"
}

function set_monitor_status() {
    sql="update instance set monitor_status=${2} where instance_id=${1}"
    ${cmd_mysql} -e "${sql}"
}

function get_service_name() {
    sql="select service_name from service where service_id=${1}"
    ${cmd_mysql} -e "${sql}"
}

function build_log_path() {
    echo "${access_log_path}/${1}.access.log"
}

function get_service_ids() {
    sql="select service_id from service where service_status=1"
    ${cmd_mysql} -e "${sql}"
}

function get_service_instances() {
    sql="select instance_id from service_mapping where service_id=${1} and is_disabled=0"
    ${cmd_mysql} -e "${sql}"
}

function get_tables() {
    sql="show tables"
    ${cmd_mysql} -e "${sql}"
}

function get_host_list() {
    sql="select host_id from host"
    ${cmd_mysql} -e "${sql}"
}

function get_host_info() {
    sql="select * from host where host_id=${1}"
    ${cmd_mysql} -e "${sql}"
}
function get_host_name() {
    sql="select host_name from host where host_id=${1}"
    ${cmd_mysql} -e "${sql}"
}
function is_skip() {
	sql="select service_id from service where service_id in
	(select service_id from service_mapping where
	instance_id=${1})
	and config_type=1"
    ${cmd_mysql} -e "${sql}"
}
function is_master(){
    sql="select writable from instance where instance_id=${1}"
    ${cmd_mysql} -e "${sql}"
}

function get_service_id_by_instance_id(){
    sql="select service_id from service_mapping where instance_id=${1}";
    ${cmd_mysql} -e "${sql}"
}

function isset_zk_instance(){
    service_id=$(get_service_id_by_instance_id ${1})
    if [ -z ${service_id} ];then
        echo ''
    else
        echo $(isset_zk_service ${service_id})
    fi
}
function isset_zk_service(){
    sql="select zk from service where service_id=${1}";
    ${cmd_mysql} -e "${sql}"
}
