#search cloud平台使用文档
---
<yongqianghuang@anjuke.com>  
v2.0 June 2013

##1.目标

统一统计管理和配置操作solr,不用操心solr的环境，不用操心每次新建solr要做很多重复的操作，也不用担心每次操作还要需要到服务器上去，通过search cloud都一切搞定。

**选择性**  

* 平台提供多个版本的solr服务
* 提供单实例、主从、cloud等方式运行实例 

**灵活性**  

* 添加、开启、关闭、重启实例通过web界面直接操作无需登陆服务端
* 不同运行方式的实例间切换只需点几个按钮
* 提供服务运行状态和查询情况展示界面
* 硬件条件满足的情况下可随意扩充

**稳定性**

* 集群式部署 消除单点故障
* 心跳检查 自动重启
* 手机和邮件报警机制

##2.架构 
两台LB分别安装LVS、Keepalived并且分别配置为Master和Backup，实现双机热备，部署平台管理应用程序，通过nginx做反向代理，将查询更新请求转发到后端对应的各个solr实例上。

        __________  +-------+
       |            |  vip  |
      arp           +-------+
       |
    -------|----------------------------------------------
    +------------+                   +------------+
    |  <Master>  |  <---failover---> |  <Backup>  |
    | Keepalived |                   | Keepalived |
    +------------+                   +------------+
           |                                |
        forward                          forward
           |                                |
       +------+                         +------+                    
       |  LB  |                         |  LB  |         
       +------+                         +------+
    -------|--------------------------------|--------------
         proxy                              |        
           |                                |         
           |                                |        
    +----------------+----------------+--------------------
    | idx10-003      | idx10-004      |
    | solr instances | solr instances |  ......
    +----------------+----------------+--------------------

cloud形式zookeeper采用集群方式分别部署在3台服务器上，每个solr instance都指向这些zookeeper
    
            +----------+  
            |zookeeper |  ----------------- solrInstance
            +----------+               
           /            \  ---------------- solrInstance
          /              \               
    +----------+     +----------+  -------- solrInstance       
    |zookeeper | --- | zookeepe | 
    +----------+     +----------+  


##3.部署

**LB**

LVS和Keepalived安装和配置 see http://www.keepalived.org/documentation.html 

**Solr Cloud Server**   


        git clone http://git.corp.anjuke.com/corp/search-cloudV3 search-cloud
        
        mysql> create database solr default character set utf8;
        mysql -u root -D solr < search-cloud/scripts/other/solr.sql
        
        添加本机配置
        vim  solr/config.prod.php  
        vim  /home/www/solr.conf.php
        
        nohup search_cloud/scripts/nginx.cron.sh
        
        crontab
        0 0 * * * /home/www/search-cloud/scripts/rsync_to_hadoop.sh
        
        

**solr**  

        git clone http://git.corp.anjuke.com/corp/search-cloudV3  search-cloud
        
        添加本机配置
        vim  search-cloud/config.prod.sh  
        vim  /home/www/solr.conf.sh  
        
        nohup  search-cloud/scripts/solr.cron.sh  
        
        crontab   
        0 1 * * * bash /home/www/search-cloud/scripts/cron_jobs/solr.stdout.rotate.sh
        */3 * * * * bash /home/www/search-cloud/scripts/cron_jobs/health.cron.sh	


        solr/scripts/execator.enable.sh
        

##4.使用方法

###4.1名词解释
**host**  
    对应一台真实的服务器，instance的实例都跑在上面   
    
**service**     
    一个service代表一个搜索服务，比如二手房房源列表的搜索就可以是一个搜索服务，对应着一个service  
    
**instance**  
    instance是service下实例，用来处理service接受到的搜索请求，一个instance对应一个solr服务  
    
处理各种搜索请求的都是instance，service相当于instance的虚拟集合，instance都跑在host上，一个service的各个instance可以分步在不同的host上。

###4.2新建服务
1. 进入http://search.corp.anjuke.com/service.php 
2. 点击添加服务，需通过域账号验证，填写serviece名称、所属部门、solr版本和配置相关信息
3. 添加配置scheme
4. 编辑启动service，默认启动了一个solr实例
5. 点击添加实例可以对同一service部署多个实例

###4.2查询服务运行状态
1. 进入http://search.corp.anjuke.com/service.php 列表
2. 列表中找到要查询的服务名称，点击进入服务详情页
3. 点击report查看服务各自状态监控

###4.3新建实例
* 在instance list页面和service detail页的instance list页都有新建instance的入口，按引导完成创建即可

###4.4管理实例
*  instance list可直接对instance进行管理，也可以到service单页对instance进行管理

###4.4操作原则
*  如果solrconfig更改过，需要重启Service才能使新配置生效,如果schema config更改过，需要重启instance才能使新配置生效
