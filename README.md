#search cloud平台使用文档
---
<yongqianghuang@anjuke.com>  
v1.0 May 15th 2013


##1.目标

统一统计管理和配置操作solr,不用操心solr的环境，不用操心每次新建solr要做很多重复的操作，也不用担心每次操作还要需要到服务器上去，通过search cloud都一切搞定。

**选择性**  

* 平台提供多个版本的solr服务
* 提供单实例、主从和sharing方式运行实例 

**灵活性**  

* 添加、开启、关闭、重启实例通过web界面直接操作无需登陆服务端
* 不同运行方式的实例间切换只需点几个按钮
* 提供服务运行状态和查询情况展示界面
* 硬件条件满足的情况下可随意扩充

**稳定性**

* 心跳检查 自动重启
* 提供手机和邮件报警机制

##2.架构 
两台LB分别安装Keepalived并且分别配置为Master和Backup，实现双机热备，部署平台管理应用程序，通过nginx做反向代理，将查询更新请求转发到后端对应的各个solr实例上。

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
    -------|-----------------------------------------------
         proxy
           |_________

##3.部署

**LB**  
>lvs  
>`wget http://www.linuxvirtualserver.org/software/kernel-2.6/ipvsadm-1.24.tar.gz`  
>`tar zxvf ipvsadm-1.24.tar.gz`  
>`cd ipvsadm-1.24`  
>`make && make install`  

>keepalive  
>`wget http://www.keepalived.org/software/keepalived-1.2.2.tar.gz`  
>`tar zxvf keepalived-1.2.2.tar.gz`  
>`cd keepalived-1.2.2`  
>`./configure`  
>`make && make install`

>config
vim /etc/keepalived/keepalived.conf

Master端

vrrp_instance VI_1 {
    state MASTER
    interface eth0
    virtual_router_id 30
    priority 200
    advert_int 1

    virtual_ipaddress {
        10.10.6.51/24 
    }
}

virtual_server 10.10.6.51 8983 {
    delay_loop 60
    persistence_timeout 10
    lb_algo rr
    lb_kind NAT
    protocol TCP
       
    real_server 10.10.6.32 8983 {
        weight 1
        TCP_CHECK {
            connect_timeout 5
            nb_get_retry 2
            delay_before_retry 3
        }
    }
    
    real_server 10.10.6.40 8983 {
        weight 1
        TCP_CHECK {
            connect_timeout 5
            nb_get_retry 2
            delay_before_retry 3
        }
    }
        
}

Backup端

vrrp_instance VI_1 {
    state BACKUP
    interface eth0
    virtual_router_id 30
    priority 100
    advert_int 1

    virtual_ipaddress {
        10.10.6.51/24 
    }
}

virtual_server 10.10.6.51 8983 {
    delay_loop 60
    persistence_timeout 10
    lb_algo rr
    lb_kind NAT
    protocol TCP
       
    real_server 10.10.6.32 8983 {
        weight 1
        TCP_CHECK {
            connect_timeout 5
            nb_get_retry 2
            delay_before_retry 3
        }
    }
    
    real_server 10.10.6.40 8983 {
        weight 1
        TCP_CHECK {
            connect_timeout 5
            nb_get_retry 2
            delay_before_retry 3
        }
    }
        
}










# Solr Cloud

## Install

### Solr Instance

    sudo aptitude install subversion mysql-client php5-cli php5-mysql
    svn checkout http://projects.dev.anjuke.com/svn/sites/utils/solr-cloud/ solr/ --username=deployer
    rm -rf solr/cloud/7700
    svn checkout http://projects.dev.anjuke.com/svn/sites/search/cloud/ solr/cloud/7700/ --username=deployer
    chmod +x solr/scripts/*.sh
    touch solr/config.prod.sh solr/config.prod.php
    nohup solr/scripts/solr.cron.sh 1>>solr/logs/solr.cron.log 2>>solr/logs/solr.cron.error.log &
    solr/scripts/execator.enable.sh

## config

* machine specified configurations

```
cp solr.config.sh.example /home/www/conf/solr.config.sh # modifiy to meet your need
cp solr.config.php.example /home/www/conf/solr.config.php # modifiy to meet your need
```
* production configurations

pick up the example and modify to meet your need

```
cp config.prod.sh.example config.prod.sh
cp config.prod.php.example config.prod.php
```

### some options need to be configured.


