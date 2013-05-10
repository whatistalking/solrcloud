#!/bin/bash - 

#
# 把昨天的access log同步到hadoop里
# 1 0 * * * /bin/bash /data1/logs/nginx/rsync_to_hadoop.sh 2>&1 > /data1/logs/nginx/rsync_to_hadoop.log
#

cd ${0%/*}
this_path=$(pwd)
cd ${this_path%scripts*}
solr_root=$(pwd)

. ${solr_root}/scripts/config.sh

host="$(hostname)"

date="$(date -d last-day +%Y%m%d)"

log_dir=${access_log_path}

remote="hadoop@10.10.6.99::alog_solr"

options="-av --progress"

empty=".empty"
> $empty

rsync -R $options $empty $remote/$date/
rsync $options $log_dir/access.log-$date $remote/$date/$date-$host.log && rsync $options $empty $remote/$date/$date-$host.log.done
