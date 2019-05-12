<?php
/**
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\Swoole\TServer\MQServer;
use LSYS\Swoole\TServer\MQServer;
abstract class RabbitMQ extends MQServer
{
    protected $mq;
    protected $exchange;
    protected $type;
    protected $queue_name;
    /**
     * @param string $exchange
     * @param string $queue_name
     * @param string $type
     * @param string $config
     */
    public function __construct($exchange,$queue_name,$type='fanout',$config=null){
        $this->exchange=$exchange;
        $this->type=$type;
        $this->queue_name=$queue_name;
        $this->mq=\LSYS\RabbitMQ\DI::get()->rabbitmq($config);
    }
    public function listen(){
        $channel=$this->channel($this->mq);
        while(count($channel->callbacks)) {
            try{
                $channel->wait();
            }catch (\ErrorException $e){
                if (strpos($e->getMessage(), "errno=10054")===false)throw $e;
                echo $e->getMessage()."\n";
                while (true){
                    try{
                        $this->mq->reconnect();
                        break;
                    }catch (\ErrorException $e){
                        if (strpos($e->getMessage(), "unable to connect")===false)throw $e;
                        sleep(3);
                        echo $e->getMessage()."\n";
                    }
                }
                $channel=$this->channel($this->mq);
            }
        }
        $channel->close();
        $this->mq->close();
    }
    /**
     * 建议根据实际需求重写
     * @param string $channel
     * @param callable $callback
     */
    protected function bind($channel,$callback){
        $channel->exchange_declare($this->exchange, $this->type, false, false, false);
        list($queue_name, ,) = $channel->queue_declare("", false, false, true, false);
        $channel->queue_bind($queue_name, $this->exchange);
        $channel->basic_consume($this->queue_name, '', false, true, false, false, $callback);
    }
    /**
     * 根据实际需求重写
     * @param mixed $connection
     */
    protected function channel($connection){
        $channel=$connection->channel();
        $callback=function ($msg){
            /**
             * @var \PhpAmqpLib\Message\AMQPMessage $msg
             */
            if($this->run($msg->delivery_info['routing_key'], $msg->getBody())){
                $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
            }
        };
        foreach ($this->topics() as $topic){
            $channel->queue_bind($this->queue_name, $this->exchange, $topic);
        }
        $this->bind($channel,$callback);
        return $channel;
    }
}