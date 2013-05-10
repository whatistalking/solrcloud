#!/bin/bash - 
#===============================================================================
#
#          FILE:  nginx.cron.sh
# 
#         USAGE:  ./nginx.cron.sh 
# 
#   DESCRIPTION:  1min执行一次，
#                 如果是主机器，则调用nginx.cron.jobscheduler.sh处理jobscheduler中的脚本
#                 如果存在文件execator_switch,则检查nginx.cron.queue.sh(处理action_queue)常驻进程
#       CREATED: 2012年09月21日 11时29分12秒 CST
#===============================================================================

cd ${0%/*}
this_path=$(pwd)
cd ${this_path%scripts*}
solr_root=$(pwd)

. ${solr_root}/scripts/config.sh


cron_queue_pid=$(ps aux |grep bash |grep nginx.cron.queue.sh |awk '{print $2}')
echo $cron_queue_pid
if [ -z $cron_queue_pid ] ;then
    nohup ${solr_root}/scripts/nginx.cron.queue.sh \
    1>>${solr_root}/logs/nginx.cron.log \
    2>>${solr_root}/logs/nginx.cron.error.log &
fi





