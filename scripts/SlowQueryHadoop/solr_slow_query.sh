#!/bin/bash -l

d=$(date +%Y%m%d -d '-1 day')

hql_file=/home/hadoop/importlog/corp/solr_slow_query.hql
log_dir=/user/corp/solr_slow_query
tmp_file=t_tmp_slow_query

echo "
add jar /home/hadoop/hive-0.8.1/lib/hive-contrib-0.8.1.jar;
add jar /home/hadoop/importlog/corp/solr_slow_query.jar;
create temporary function ssq as 'com.anjuke.corp.SolrSlowQuery';

insert overwrite table alog_solr.${tmp_file}
select sn, url, avg(rt) avg_time, count(rt) query_count
from
(
  select split(request_url,'/')[1] sn, ssq(request_url) url, request_time rt 
  from 
  alog_solr.d_$d
) a 
where sn is not null
group by sn, url; 

insert overwrite directory '$log_dir/$d-avg'
select concat(b.sn,' ',b.url,' ',b.avg_time,' ',b.query_count)
from
(
select sn, url, avg_time*1000 avg_time, query_count
from
   alog_solr.${tmp_file}
   order by sn, avg_time desc
) b;

insert overwrite directory '$log_dir/$d-count'
select concat(b.sn,' ',b.url,' ',b.avg_time,' ',b.query_count)
from
(
select sn, url, avg_time*1000 avg_time, cast(query_count as int) query_count
from
   alog_solr.${tmp_file}
   order by sn, query_count desc
) b;

" > $hql_file

hive -f $hql_file

