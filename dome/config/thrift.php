<?php
use Thrift\Transport\TSocket;
use LSYS\Swoole\Thrift\Server\TSwooleSocket;
use LSYS\Swoole\Thrift\Server\TSwooleSocketPool;
return array(
    //普通方式连接配置
    "client"=>array(
        'socket'=>TSocket::class,
        'args'=>array(
            '127.0.0.1','9809'
        ),
        //以下参数DomeMYClientProxy 自动填充参数使用到,非必须,可根据实际需求自行定制
        "app"=>"app1",
        "token"=>"111111",
        "platform"=>"client1",
    ),
    //协程客户端连接
    "client_"=>array(
        'socket'=>TSwooleSocket::class,
        'args'=>array(
            'swoole.clienl_dome'
        ),
        //以下参数DomeMYClientProxy 自动填充参数使用到,非必须,可根据实际需求自行定制
        "app"=>"app1",
        "token"=>"111111",
        "platform"=>"client1",
    ),
    //协程客户端连接池方式连接
    "client_pool"=>array(
        'socket'=>TSwooleSocketPool::class,
        'args'=>array(
            'swoole.client','app*'
        ),
        //以下参数DomeMYClientProxy 自动填充参数使用到,非必须,可根据实际需求自行定制
        "app"=>"app1",
        "token"=>"111111",
        "platform"=>"client1",
    ),
);
