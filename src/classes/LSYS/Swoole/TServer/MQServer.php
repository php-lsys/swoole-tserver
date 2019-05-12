<?php
namespace LSYS\Swoole\TServer;
use LSYS\MQS\MQListen;
use LSYS\MQS\DataDeCode;
use LSYS\MQS\DataCode\Simple;
abstract class MQServer
{
    private $listen=[];
    /**
     * 添加监听主题
     * @param string $topic 主题名
     * @param array $class 处理类列表
     * @param DataDeCode $code 消息解码对象
     * @return static
     */
    public function addTopic($topic,array $class,DataDeCode $code=null){
        if(is_null($code))$code=new Simple($topic);
        $this->listen[$topic]=new MQListen($code);
        $this->listen[$topic]->setRuner($topic,$class);
        return $this;
    }
    /**
     * 得到当前MQ监听的主题列表
     * @return array
     */
    protected function topics(){
        return array_keys($this->listen);
    }
    /**
     * 执行执行主题的消息
     * @param string $topic
     * @param string $msg
     * @return void|boolean
     */
    protected function run($topic,$msg){
        if(!isset($this->listen[$topic]))return ;
        $result=$this->listen[$topic]->exec($topic, $msg);
        $error=false;
		if(is_array($result)){
			$loger=\LSYS\Loger\DI::get()->loger();
			$loger->batchStart();
			foreach ($result as $v){
				if ($v instanceof \Exception){
					$loger->add(\LSYS\Loger::ERROR, $v);
					$error=true;
				}
			}
			$loger->batchEnd();
		}
        return !$error;
    }
    /**
     * 后台监听
     */
    abstract public  function listen();
}