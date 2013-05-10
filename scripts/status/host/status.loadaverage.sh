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
    uptime=$(ssh ${host_ip} uptime)
    last_5m_load=$(echo ${uptime}|awk -F , '{print $(NF-1)}'|awk '{print $1}')
    [ -z ${last_5m_load} ] && continue

#    echo "replace into status_log_host_loadaverage set log_idc='${idc}',log_host='${default_host}',log_type='host',log_name='loadaverage',target_id='${id}',log_value='${last_5m_load}',log_time='${log_time}'"
    sql_log="replace into status_log_host_loadaverage set log_idc='${idc}',log_host='${default_host}',log_type='host',log_name='loadaverage',target_id='${id}',log_value='${last_5m_load}',log_time='${log_time}'"
    ${cmd_mysql_log} -e "${sql_log}"
done

