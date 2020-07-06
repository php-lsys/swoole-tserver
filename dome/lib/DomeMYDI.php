<?php
use Information\DomeProductClient;
use LSYS\DI\SingletonCallback;
use LSYS\MQS\MQSender;
use LSYS\MQS\Sender\SendCall;
use LSYS\MQS\DataCode\Simple;

/**
 * @method DomeProductClient|DomeMYClientProxy product(ClientProxy $client=null,$config=null)
 * @method MQSender mq1()
 */
class DomeMYDI extends \LSYS\DI
{

    /**
     * 默认客户端配置
     * @var string default config
     */
    public static $config = 'thrift.client';
    /**
     *
     * @return static
     */
    public static function get()
    {
        $di = parent::get();
        // 这里定义客户端实例得到的方法
        ! isset($di->product) && $di->product(new \LSYS\DI\MethodCallback(function(){
            $config=\LSYS\Config\DI::get()->config(self::$config);
            return DomeMYClientProxy::create(DomeProductClient::class, $config);
        }));
        ! isset($di->mq1) && $di->mq1(new SingletonCallback(function(){
            return new MQSender(new SendCall(function($topic,$msg,$dealy){
                //这里可以使用连接池处理
                $mq=new \LSYS\Redis\MQ(\LSYS\Redis\DI::get()->redis());
                $data=$mq->push($topic,$msg,$dealy);
                return $data;
            }),new Simple("mytopic"));
        }));
        return $di;
    }
}
