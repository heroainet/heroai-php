# ZeroAI-PHP for Framework
目录
====

[简介](#简介)

[安装](#安装)

[编码规范](#编码规范)

[SQL规范](#SQL规范)

[ZeroAI-Utils 生产环境一键安装包](#ZeroAI-Utils)


简介
====

一款经过生产环境检验(日PV10亿级)的轻量级PHP框架。

```shell
#支持Web和Console两种模式，单文件入口，自动识别web和cli环境，创建web下/console的application。
php index.php
```

支持Console环境下(主要适应于LINUX CENTOS 7)的Daemon守护进程模式。

   ```shell
   #实现了经典的Master-Worker模式。
   php index.php -daemon=start -id=zeroaid
   
   #可扩展为TCP服务端程序，定时器，IO异步事件驱动等模式，能够365xx24稳定运行。
   ```

支持一键打包成单文件可执行程序。
   ```shell
   #编译
   php index.php --build
   
   #运行生成的phar单文件程序
   php zeroaid.phar
   ```

安装
===

```shell
git clone https://github.com/zeroainet/zeroai-php-webpack.git

cd zeroai-php-webpack

#兼容composer安装zeroai-php库
composer install zeroai-php@dev

#直接git下载
mkdir  lib/ && cd lib
git clone https://github.com/zeroainet/zeroai-php.git

#运行
php index.php

#编译
php index.php --build

#开启守护进程
php index.php -d

#具体配置文件
vi application/config/profile.php


```

ZeroAI-Utils
====

框架所在的生产环境 ,Linux(CentOS7X_64) +openresty(nginx)+Mysql+PHP+Redis一键安装包.

项目地址: https://github.com/zeroainet/zeroai-utils.git

