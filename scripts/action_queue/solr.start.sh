#!/bin/bash

cd ${0%/*}
this_path=$(pwd)
cd ${this_path%scripts*}
solr_root=$(pwd)

# . /etc/profile
. ${solr_root}/scripts/config.sh

usage="Usage: ${prog} [instance_id]"

if [ -z "${1}" ]
then
    echo ${usage}
    exit 2
fi

port=$(get_port_num ${1})
if [ -z "${port}" ]
then
    echo "Instance[${1}] not exists"
    exit 1
fi

pid=$(get_solr_pid ${port})
if [ -n "${pid}" ]
then
    echo "Process[${pid}] exists"
    exit 1
fi

mem=$(get_use_memory ${1})

echo "Solr start instance[${1}]"
instance_root=$(build_instance_root ${solr_root} ${port})
cmd_solr="${instance_root}/server/bin/start.sh"

if [ ! -f "${cmd_solr}" ]
then
    echo "Script not exists (Not be deployed)"
    exit 1
fi

#如果有zk并且是master就使用zookeeper参数
zk=$(isset_zk_instance ${1})
if [[ $(is_master ${1}) -eq 1 && -n ${zk} ]]
then
    echo "${cmd_solr} ${mem} ${port} ${zk} &"
    /bin/bash -l ${cmd_solr} ${mem} ${port} ${zk} > ${instance_root}/server/jetty.log 2>&1 &
    exit $?
else
    echo "${cmd_solr} ${mem} &"
    /bin/bash -l ${cmd_solr} ${mem}  > ${instance_root}/server/jetty.log 2>&1  &
    exit $?
fi
#jetty启动时日志默认输出到命令行，这里定向记录到jetty.log 2>&1，用于idx10-012:7703 2013_01_28 10:06无法启动且无日志的追查
