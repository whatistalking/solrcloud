#!/bin/bash -l
cd ${0%/*}
this_path=$(pwd)
cd ${this_path%scripts*}
solr_root=$(pwd)

. ${solr_root}/scripts/config.sh

log_time=$(date +"%F %H:%M:00")

host_ids=$(get_host_list)

for id in ${host_ids}
do
    host_info=$(get_host_info ${id})
    host_ip=$(echo ${host_info}|awk '{print $3}')

    memory=$(ssh ${host_ip} 'free -m')
    total_memory=$(echo ${memory}|awk '{print $8}')   
    [ -z ${total_memory} ] && continue     

    free_memory=$(echo ${memory}|awk '{print $10}')
    buffered=$(echo ${memory}|awk '{print $12}')
    cached_memory=$(echo ${memory}|awk '{print $13}')

    used_memory=$(echo "${total_memory}-${free_memory}-${buffered}-${cached_memory}"|bc -l)

#    echo "replace into status_log_host_memoryused set log_idc='${idc}',log_host='${default_host}',log_type='host',log_name='memoryused',target_id='${id}',log_value='${used_memory}',log_time='${log_time}'"
    sql_log="replace into status_log_host_memoryused set log_idc='${idc}',log_host='${default_host}',log_type='host',log_name='memoryused',target_id='${id}',log_value='${used_memory}',log_time='${log_time}'"
    ${cmd_mysql_log} -e "${sql_log}"
done

