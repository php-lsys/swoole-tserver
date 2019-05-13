<?php
return array(
    "mysql"=>array(
        "try"=>true,//发送错误重试次数,设置为TRUE为不限制
        "sleep"=>1,//断开连接重连暂停时间
        "table_prefix"=>"t_",//断开连接重连暂停时间
        "master"=>array(
            "size"=>5,//队列长度
			//设置下面两个会清理释放空闲链接
			//"keep_size"=>1,//空闲时保留链接数量
			//"keep_time"=>300,//空闲超过300关闭链接
            "weight"=>1,//权重
            "connection"=>array(//这里配置根据每个连接不同自定义.这里是MYSQL配置
                'host' => '127.0.0.1',
                'port' => 3306,
                'user' => 'root',
                'password' => '110',
                'fetch_mode' 		=> 1,
                'database' => 'test',
            )
        ),
    ),
    "tokens"=>array(
        "client1"=>"111111"  
    ),
    "server"=>array(
        //"name"=>"myapp",
        //"version"=>"0.0.1",
        "mq"=>array(
            "daemon"=>0,
            //"pid_file"=>"mq.pid"
            "apps"=>array(
                "app1"=>array(
                    //"decode"=>[Simple::class,['mytopic','']],
                    //"server"=>[\LSYS\Swoole\TServer\MQServer\RabbitMQ::class,['changge','aaa']],
                    //"process"=>1,//监听消息处理进程数量
                    //"attach_process"=>1,//Redis延时消息派发进程
                    "server"=>[\LSYS\Swoole\TServer\MQServer\RedisMQ::class],
                    "topic"=>[
                        //主题=>[处理类] 处理类可以多个,要有先后顺序在类里实现权重接口
                        'mytopic'=>[MYMsgDome::class]
                    ],
                ),
                "app2"=>array(
                    //"decode"=>
                    "topic"=>['mytopic'=>["mq"]],
                ),
            )
        ),
        "runer"=>array(
            "app1"=>array(
                "go"=>true,
                "class"=>[DomeModelBuild::class],
                "method"=>['build']
            )
        ),
        "app"=>array(
            //"daemon"=>0,
            "apps"=>array(
                "app1"=>array(
                    "port"=>"9809",
                    "setting"=>array(
                        'worker_num' => 2,
                        //'task_worker_num'=>1,
                    )
                ),
                "app2"=>array(
                    "port"=>"9891",
                    "setting"=>array(
                        'worker_num' => 4,
                    )
                ),
            )
        ),
    )
);
