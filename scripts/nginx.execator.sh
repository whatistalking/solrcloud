#!/bin/bash

cd ${0%/*}
this_path=$(pwd)
cd ${this_path%scripts*}
solr_root=$(pwd)

. ${solr_root}/scripts/config.sh


[ ! -f ${execator_switch} ] && exit 0

let i=0
while true
do
    queue=$(get_service_queue ${host_id})
    [[ -z ${queue} ]] && break

    queue_id=$(echo ${queue} | awk '{print $1}')
    action_id=$(echo ${queue} | awk '{print $2}')
    target_id=$(echo ${queue} | awk '{print $3}')
    script_name=$(echo ${queue} | awk '{print $4}')
    session_id=$(echo ${queue} | awk '{print $5}')

    echo -ne "${current_time}\t${queue_id}\t${script_name}\tservice[${target_id}]\t"

    pending=$(get_instance_queue_pending ${queue_id})
    if [ -n "${pending}" ]
    then
        echo "pending queue[${pending}]"
        exit 0
    fi

    ${solr_root}/scripts/${script_name} ${target_id} >> ${solr_root}/logs/nginx.execator.$(date +%F).log 2>&1

    retval=$?
    if [ ${retval} == "0" ]
    then
        echo "success"
        set_queue_succ ${queue_id}
    else
        echo "failure"
        set_queue_fail ${queue_id}
    fi

    set_service_unlock ${target_id}

    echo -e "${current_time}\t0\tnginx.execator sleep"

    ((i++))
    [ ${i} -eq 10 ] && break

    sleep 3
done
