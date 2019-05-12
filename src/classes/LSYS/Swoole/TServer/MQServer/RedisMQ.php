<?php
/**
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\Swoole\TServer\MQServer;
use LSYS\Swoole\TServer\MQServer;
use LSYS\Swoole\TServer\MQServerProcess;
class RedisMQ extends MQServer implements MQServerProcess
{
    protected $mq;
    public function __construct($config=null) {
        $this->mq=\LSYS\Redis\DI::get()->redisMQ($config);
    }
    public function pop($topics,&$ack){
        return $this->mq->pop($topics,false,$ack,3600);
    }
    public function listen()
    {
        $redismq=$this->mq;
        $topic=$this->topics();
        while (true){
            $ack_key=null;
            $data=$redismq->pop($topic,false,$ack_key);
            if(count($data)!=2)continue;
            list($topic,$msg)=$data;
            $this->run($topic, $msg)&&$redismq->ack($topic, $ack_key,$data);
        }
    }
    public static function attachProcess($topics=array()){
        $redismq=\LSYS\Redis\DI::get()->redisMQ();
        return intval($redismq->delayDaemon($topics));
    }
}