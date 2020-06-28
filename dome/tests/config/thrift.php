<?php
use Thrift\Transport\TSocket;
return array(
    //非协程版,在SERVER环境中不可用
    "client"=>array(
        'socket'=>TSocket::class,
        'args'=>array(
            '127.0.0.1','9809'
        ),
        "app"=>"app1",
        "token"=>"test",
        "platform"=>"test",
    ),
);
