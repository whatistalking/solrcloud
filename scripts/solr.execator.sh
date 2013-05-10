#!/bin/bash

cd ${0%/*}
this_path=$(pwd)
cd ${this_path%scripts*}
solr_root=$(pwd)

. ${solr_root}/scripts/config.sh

[ ! -f ${execator_switch} ] && exit 0
if [ -z ${host_id} ]
then
    echo "No host_id specified"
    exit 1
fi

function get_queue() {
    queue=$(get_service2_queue ${1})
    if [ -z "${queue}" ];then
        queue=$(get_instance_queue ${1})
    fi
    echo $queue
}
let i=0
while true
do
    queue=$(get_queue ${host_id})
    [[ -z ${queue} ]] && break
    queue_id=$(echo ${queue} | awk '{print $1}')
    action_id=$(echo ${queue} | awk '{print $2}')
    target_id=$(echo ${queue} | awk '{print $3}')
    script_name=$(echo ${queue} | awk '{print $4}')

    echo -ne "${current_time}\t${queue_id}\t${script_name}\tinstance[${target_id}]\t"
    
    pending=$(get_service_queue_pending ${queue_id})
    
    if [ -n "${pending}" ]
    then
        echo "pending queue[${pending}]"
        exit 0
    fi
    
    ${solr_root}/scripts/${script_name} ${target_id} >> ${solr_root}/logs/solr.execator.$(date +%F).log 2>&1
    retval=$?
    if [ ${retval} == "0" ]
    then
        echo "success"
        case ${action_id} in
        3)
            instance_status=2;;
        4)
            instance_status=3;;
        5)
            instance_status=1;;
        *)
            ;;
        esac
        [[ -n ${instance_status} ]] && set_instance_status ${target_id} ${instance_status}
        set_queue_succ ${queue_id}
    else
        echo "failure"
        set_queue_fail ${queue_id}
    fi

    set_instance_unlock ${target_id}

    echo -e "${current_time}\t0\tsolr.execator sleep"

    ((i++))
    [ ${i} -eq 10 ] && break

    sleep 3
done
