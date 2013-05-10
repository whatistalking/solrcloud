#!/bin/bash - 
#===============================================================================
#
#          FILE:  solr.cron.sh
# 
#         USAGE:  ./solr.cron.sh 
# 
#   DESCRIPTION:  1min执行一次，
#                 如果是主机器，则调用solr.cron.jobscheduler.sh处理jobscheduler中的脚本
#                 如果存在文件execator_switch,则检查solr.cron.queue.sh(处理action_queue)常驻进程
#       CREATED: 2012年09月21日 11时29分12秒 CST
#===============================================================================

cd ${0%/*}
this_path=$(pwd)
cd ${this_path%scripts*}
solr_root=$(pwd)

. ${solr_root}/scripts/config.sh

${solr_root}/scripts/solr.cron.jobscheduler.sh >> ${solr_root}/logs/jobscheduler.log


cron_queue_pid=$(ps aux |grep bash |grep solr.cron.queue.sh |awk '{print $2}')
if [ -z $cron_queue_pid ] ;then
    nohup ${solr_root}/scripts/solr.cron.queue.sh \
    1>>${solr_root}/logs/solr.cron.log \
    2>>${solr_root}/logs/solr.cron.error.log &
fi





