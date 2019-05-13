<?php
use LSYS\Swoole\TServer\Server\Middleware\TokenMiddleware;
use LSYS\Swoole\TServer\Server\Middleware\BreakerMiddleware;
use LSYS\Swoole\Thrift\Server\SwooleSubject;
use LSYS\Swoole\Thrift\Server\SwooleEvent;
use LSYS\EventManager\CallbackObserver;
/**
 * 配置服务器
 */
class DomeMyServer extends \LSYS\Swoole\TServer\Server
{
    public function bootstrap(){
        //加载thrift库
        $this->thriftLoader()->registerDefinition("Information",dirname(__DIR__)."/gen-php");
        TokenMiddleware::registerDefinition($this->thriftLoader());
        
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
        $this->eventManager()->attach((new SwooleSubject(SwooleEvent::WorkerStart))->attach(new CallbackObserver(function(){
            if(!$this->swoole()->taskworker){
                //启动任务,必须在非TASK进程.若使用task,必须设置 task_worker_num
               // $this->taskManager()->task(DomeTask::class, ["aa"]);
                //启动定时任务
//                 $this->timerManager()->tick(1000,function(){
//                     var_dump("worker");
//                 });
            }else{
                //注意:TASK进程派发task会被忽略
//                 $this->timerManager()->tick(1000,function(){
//                     var_dump("task");
//                 });
            }
        })));
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