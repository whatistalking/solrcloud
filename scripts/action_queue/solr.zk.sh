#!/bin/bash -l

cd ${0%/*}
this_path=$(pwd)
cd ${this_path%scripts*}
solr_root=$(pwd)

. ${solr_root}/scripts/config.sh
#指定host执行的指定service的zk，没有就起来，有就重启

usage="Usage: ${prog} [service]"

if [ -z ${1} ]
then
    echo ${usage}
    exit 2
fi

service_id=${1}
zk_port=10${service_id}

#每个service复制出来一个zk，配置并启动
#决定着zookeeper.out文件的位置
cd ${solr_root}/zookeeper/zk-${zk_port}
#完全停止zookeeper
${solr_root}/zookeeper/zk-${zk_port}/bin/zkServer.sh stop
rm -r ${solr_root}/zookeeper/zk-${zk_port}
cp -r ${solr_root}/zookeeper/zk-000 ${solr_root}/zookeeper/zk-${zk_port}
#配置zoo.cfg
cd ${solr_root}/zookeeper/zk-${zk_port}
echo "tickTime=2000" > ${solr_root}/zookeeper/zk-${zk_port}/conf/zoo.cfg
echo "initLimit=10" >> ${solr_root}/zookeeper/zk-${zk_port}/conf/zoo.cfg
echo "yncLimit=5" >> ${solr_root}/zookeeper/zk-${zk_port}/conf/zoo.cfg
echo "dataDir=${solr_root}/zookeeper/zk-${zk_port}/data" >> ${solr_root}/zookeeper/zk-${zk_port}/conf/zoo.cfg
echo "clientPort=${zk_port}" >> ${solr_root}/zookeeper/zk-${zk_port}/conf/zoo.cfg
#echo "dataLogDir=${solr_root}/zookeeper/zk-${zk_port}/" >> ${solr_root}/zookeeper/zk-${zk_port}/conf/zoo.cfg
#启动
bash ${solr_root}/zookeeper/zk-${zk_port}/bin/zkServer.sh restart

