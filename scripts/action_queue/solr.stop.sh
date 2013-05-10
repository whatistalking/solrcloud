#!/bin/bash

cd ${0%/*}
this_path=$(pwd)
cd ${this_path%scripts*}
solr_root=$(pwd)

. ${solr_root}/scripts/config.sh

#JAVA_HOME=""

usage="Usage: ${prog} [instance_id]"

if [ -z "${1}" ]
then
    echo ${usage}
    exit 2
fi

port=$(get_port_num ${1})
if [ -z "${port}" ]
then
    echo "Instance ${1} not exists"
    exit 1
fi

pid=$(get_solr_pid ${port})
if [ -z "${pid}" ]
then
    echo "Process not exists"
    exit 0
fi

echo "Solr stop instance[${1}]"
kill -9 $pid
exit $?
