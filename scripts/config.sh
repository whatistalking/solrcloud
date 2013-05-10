#!/bin/bash
mysql_db_name="solr"
mysql_db_host="localhost"
mysql_db_user="solr"
mysql_db_pass="solr"
cmd_mysql="mysql -h ${mysql_db_host} -u ${mysql_db_user} -p${mysql_db_pass} -D ${mysql_db_name} -ANs"

mysql_log_db_name="solr_log"
mysql_log_db_host="localhost"
mysql_log_db_user="solr_log"
mysql_log_db_pass="solr_log"
cmd_mysql_log="mysql -h ${mysql_log_db_host} -u ${mysql_log_db_user} -p${mysql_log_db_pass} -D ${mysql_log_db_name} -ANs"

cmd_php="php"
cmd_nginx_rh="/etc/init.d/nginx"
execator_switch="/home/www/.execator.enable"
current_time=$(date "+%F %T")
mysql_data_dir="/var/lib/mysql"
idc=sysdev

# nginx.log.sh / nginx.speed.sh 里会用到 标明当前是哪台主机，和IDX10-003 最后的数字对应
host=-1

# nginx.execator.sh / solr.execator.sh 里会用到，ID是DB里主机对应的ID
host_id=-1
zk_host_id=1

access_log_path="/var/log/nginx"

PYTHON_BIN="/usr/bin/evn python"

[ -f ${solr_root}/config.prod.sh ] && . ${solr_root}/config.prod.sh

. ${solr_root}/scripts/function.sh
