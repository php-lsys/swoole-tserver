<?php
use LSYS\Swoole\TServer\Server\Middleware\TokenMiddleware;
use LSYS\Swoole\TServer\Server\Middleware\BreakerMiddleware;
use LSYS\Swoole\Thrift\Server\SwooleSubject;
use LSYS\Swoole\Thrift\Server\SwooleEvent;
use LSYS\Swoole\TServer\SwooleObserver\TaskObserver;
use LSYS\Swoole\TServer\Server\Swoole\TaskManager;
/**
 * 配置服务器
 */
class DomeMyServer extends \LSYS\Swoole\TServer\Server
{
    protected $task;
    public function taskManager(){
        if(!$this->task)$this->task=new TaskManager($this);
        return $this->task;
    }
    public function bootstrap(){
        //加载thrift库
        $this->thriftLoader()->registerDefinition("Information",dirname(__DIR__)."/gen-php");
        TokenMiddleware::registerDefinition($this->thriftLoader());
        
        $this->taskManager()->task(DomeTask::class, ['data']);
        //这里批量注册DI
       
        //加载客户端秘钥
        $this->middleware=[
            new DomeMyMiddleware(),
            //内置TOKEN验证中间件
            new TokenMiddleware($this,function($client){
                $tokens=\LSYS\Config\DI::get()->config("swoole.tokens")->asArray();
                return $tokens[$client]??'';
            }),
            //内置服务治理中间件
            (new BreakerMiddleware($this))
            ->setRequestLimit([
                "test"=>[60=>1000]// "方法名"=>['时间'=>'限制次数']
            ])
            ->setIpLimit(function($try=0){
                //非连接池使用方法
                $redis=\LSYS\Swoole\Coroutine\DI::get()->swoole_redis();
                //if($try>0)$redis->close();//重试时候可以关闭.
                return $redis;
            },function($redis){
                //如果使用redis连接池,可以在这里把连接还回连接池
            },[//内置服务治理中间件
                "test"=>[60=>1]// "方法名"=>['时间'=>'限制次数']
            ]),

        ];
    }
    public function handler()
    {
        return new DomeMyHandler($this);
    }
    public function processor($handler)
    {
        return new \Information\DomeProductProcessor($handler);
    }
}