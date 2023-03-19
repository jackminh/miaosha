# 此项目是用于秒杀活动中后台基本实现
demo.sql 是数据库文件,ih_store是商品库存信息,商品默认数量是500，以下是以500进行压力测试
环境：
    操作系统是Ubuntu 18.04.5 LTS,
    virtual box的homestead
    宿主机macbook air,4G内存,1.6G Intel core i5
压力测试工具: webbench
1分钟模拟1000个并发,发送请求成功46220个
webbench -c 1000 -t 60 http://miaosha.test/index.php
以下是测试输出结果:
-----------------------------------------------------------------------
Webbench - Simple Web Benchmark 1.5
Copyright (c) Radim Kolar 1997-2004, GPL Open Source Software.
Benchmarking: GET http://miaosha.test/index.php
1000 clients, running 60 sec.

Speed=46220 pages/min, 237133 bytes/sec.
Requests: 46220 susceed, 0 failed.
-------------------------------------------------------------------------
条件：每个用户限抢一件商品，1分钟内模拟1000个client,最终成功生成订单498个

