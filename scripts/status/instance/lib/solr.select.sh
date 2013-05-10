#!/bin/bash

cd ${0%/*}
this_path=$(pwd)
cd ${this_path%scripts*}
solr_root=$(pwd)

t=`date +"%Y-%m-%d"`
selectlog=${solr_root}/logs/${t}
date +'%Y%m%d %H:%M:%S' >> ${selectlog}


. ${solr_root}/scripts/config.sh

usage="Usage: ${prog} [instance_id]"

if [ -z "${1}" ]
then
    echo ${usage}
    exit 21
fi

port=$(get_port_num ${1})
if [ -z "${port}" ]
then
    echo "Instance ${1} not exists" >> ${selectlog}
    exit 11
fi

host=$(get_host_ip ${1})
url="http://${host}:${port}/solr/select/?q=*:*&rows=0"
log="/dev/shm/solr.select.${1}"

curl -v -s --connect-timeout 2 --max-time 5 ${url} 1>${log} 2>&1

timeout=$(cat ${log} | grep "Timeout")
if [ -n "${timeout}" ]
then
    echo "Instance ${1} select fail: connect timed out!" >> ${selectlog}
    cat ${log} >> ${selectlog}  #抓一下错误日志
    exit 12
fi

http_status=$(cat ${log} | grep "< HTTP\/1.1 200 OK")
if [ -z "${http_status}" ]
then
    echo "Instance ${1} select fail: http status code is not 200!" >> ${selectlog}
    cat ${log} >> ${selectlog}  #抓一下错误日志

    if [ -n "$(cat ${log} | grep 'OutOfMemoryError')" ];then
        exit 15  #内存不足，添加1G重启
    fi
    if [ -n "$(cat ${log} | grep 'connect to host')" ];then
        exit 16  #后台挂了，重启
    fi
    
    exit 13
fi

num_found=$(cat ${log} | grep "numFound" | sed -e 's/.\+numFound="\([0-9]\+\)".\+/\1/')
if [ -z "${num_found}" ] || [ "${num_found}" == "0" ]
then
    echo "Instance ${1} select fail: zero result!" >> ${selectlog}
    subscript=`expr ${#phonearr[@]} - 1`
    for i in `seq 0 ${subscript}`;do
        echo "num=${phonearr[$i]}&content=id为${1}的instance的document为0,请及时检查"
        # curl -d "num=${phonearr[$i]}&content='id为${1}的instance的document为0,请及时检查'&uid=10000" http://10.10.6.202/send.php
    done 
    exit 0
fi

let qtime=$(cat ${log} | grep "QTime" | sed -e 's/.\+<int name="QTime">\([0-9]\+\)<\/int>.\+/\1/')
if [ $qtime -gt 1000 ]
then
    echo "Instance ${1} select fail: slow query!" >> ${selectlog}
    exit 22
fi

[ -f "${log}" ] && rm -f ${log}

echo "Instance ${1} select succ!"
exit 0

