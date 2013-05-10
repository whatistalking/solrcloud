#!/bin/bash

cd ${0%/*}
this_path=$(pwd)
cd ${this_path%scripts*}
solr_root=$(pwd)
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
if [ -z "${pid}" ]
then
    echo "Instance[${1}] is not running"
    exit 1
fi

host=$(get_host_ip ${1})

echo "Solr optimize instance[${1}]"
#instance_root=$(build_instance_root ${solr_root} ${port})
#optimize_path=${instance_root}/server/scripts/optimize
#if [ -f ${optimize_path} ]
#then
#    cmd_solr="${optimize_path} -h ${host} -p ${port}"
#    /bin/bash ${cmd_solr}
#else
#    cmd_solr="${host}:${port}/solr/update/?optimize=true"
#    rs=`curl ${cmd_solr}`
#    if [[ $? != 0 ]]
#    then
#        exit 1
#    fi
#    rc=`echo $rs|cut -f2 -d'"'`
#    if [[ $? != 0 ]]
#    then
#      echo $rs | grep '<lst name="responseHeader"><int name="status">0</int>' > /dev/null 2>&1
#      if [[ $? != 0 ]]
#      then
#        exit 2
#      fi  
#    fi
#fi
cmd_solr="${host}:${port}/solr/update/?optimize=true"
rs=`curl ${cmd_solr}`
if [[ $? != 0 ]]
then
    exit 1
fi
echo $rs | grep '<lst name="responseHeader"><int name="status">0</int>' > /dev/null 2>&1
if [[ $? != 0 ]]
then
    exit 2
fi  



exit $?
