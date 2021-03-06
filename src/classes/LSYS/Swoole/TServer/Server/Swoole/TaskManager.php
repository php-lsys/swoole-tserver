<?php
/**
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\Swoole\TServer\Server\Swoole;
use LSYS\Swoole\TServer\Server;
use LSYS\Swoole\TServer\Exception;
use LSYS\EventManager\Event;
use LSYS\Swoole\Thrift\Server\EventManager\SwooleEvent;
class TaskManager 
{
    protected $server;
    public function __construct(Server $server) {
        $this->server=$server;
        $this->server->eventManager()->attach(new \LSYS\EventManager\EventCallback(SwooleEvent::Task,[$this,"onTask"]));
        $this->server->eventManager()->attach(new \LSYS\EventManager\EventCallback(SwooleEvent::Finish,[$this,"onFinish"]));
    }
    /**
     * 发送任务,在task进程不可用,返回false
     * @param string $task_runer
     * @param array $data
     * @param int $dst_worker_id
     * @param callable $callback
     * @throws Exception
     * @return int|false
     */
    public function task($task_runer,array $data, $dst_worker_id=-1, $callback=null){
        if(!class_exists($task_runer)||!(new \ReflectionClass($task_runer))->implementsInterface(TaskRuner::class)){
            throw new Exception("task args need implements taskRuner");
        }
        if(!is_object($this->server->swoole())){
            throw new Exception("not find swoole object,may be not start server");
        }
        if($this->server->swoole()->taskworker)return false;
        return $this->server->swoole()->task([$task_runer,$data], $dst_worker_id, function(\Swoole\Server $serv, $task_id, $data)use($callback){
            if(!is_callable($callback)
                ||!$data=@json_decode($data,true)
                ||!is_array($data))return ;
            return call_user_func($callback,$serv,$task_id,$data[2]??null);
        });
    }
    /**
     * 发送同步任务,在task进程不可用,返回false
     * @param string $task_runer
     * @param array $data
     * @param int $dst_worker_id
     * @throws Exception
     * @return int|false
     */
    public function taskwait($task_runer,array $data, $dst_worker_id=-1){
        if(!class_exists($task_runer)||!(new \ReflectionClass($task_runer))->implementsInterface(TaskRuner::class)){
            throw new Exception("task args need implements taskRuner");
        }
        if(!is_object($this->server->swoole())){
            throw new Exception("not find swoole object,may be not start server");
        }
        if($this->server->swoole()->taskworker)return false;
        return $this->server->swoole()->taskwait([$task_runer,$data], $dst_worker_id);
    }
    /**
     * 执行批量任务,在task进程不可用,返回false
     * $task 参数：[
     *     task_class,[data1,data2],
     *     task_class,[data1,data2],
     * ]
     * @param array $tasks
     * @param int $timeout
     * @return array
     */
    public function taskWaitMulti(array $tasks,$timeout){
        foreach ($tasks as $v){
            assert(isset($v[0])&&class_exists($v[0])&&(new \ReflectionClass($v[0]))->implementsInterface(TaskRuner::class));
            assert(isset($v[1])&&is_array($v[1]));
        }
        if(!is_object($this->server->swoole())){
            throw new Exception("not find swoole object,may be not start server");
        }
        if($this->server->swoole()->taskworker)return false;
        return $this->server->swoole()->taskWaitMulti($tasks,$timeout);
    }
    /**
     * swoole回调函数
     * @param Event $event
     */
    public function onTask(Event $event) {
        $data=$event->data();
        if (!is_array($data))return ;
        if(\Swoole\Coroutine::getuid()==-1){
            call_user_func_array([$this,"onTask_"], $data);
        }else{
            call_user_func_array([$this,"onTaskCo_"], $data);
        }
    }
    /**
     * @param mixed $data
     * @return TaskRuner||NULL
     */
    protected function deRuner($data){
        if(!is_array($data)||!isset($data[0])||!isset($data[1]))return;
        list($runer,$data)=$data;
        if(!class_exists($runer))return;
        $ref=new \ReflectionClass($runer);
        if(!$ref->isSubclassOf(TaskRuner::class))return ;
        try{
            return $ref->newInstanceArgs($data);
        }catch (\Exception $e){
            \LSYS\Loger\DI::get()->loger()->add(\LSYS\Loger::ERROR, $e);
        }
    }
    protected function onTask_(\Swoole\Server $serv, int $task_id, int $src_worker_id, $data){
        $obj=$this->deRuner($data);
        if(!is_object($obj))return ;
        try{
            $obj->onTask($this->server,$serv,$task_id,$src_worker_id,0,function($fdata)use($obj,$data,$serv){
                $serv->finish(json_encode([get_class($obj),$data[1],$fdata],JSON_UNESCAPED_UNICODE));
            });
        }catch (\Exception $e){
            \LSYS\Loger\DI::get()->loger()->add(\LSYS\Loger::ERROR, $e);
        }
    }
    protected function onTaskCo_($serv, $task){
        $obj=$this->deRuner($task->data);
        if(!is_object($obj))return ;
        try{
            $obj->onTask($this->server,$serv,$task->id,$task->worker_id,$task->flags,function($fdata)use($obj,$task){
                $task->finish(json_encode([get_class($obj),$task->data[1],$fdata],JSON_UNESCAPED_UNICODE));
            });
        }catch (\Exception $e){
            \LSYS\Loger\DI::get()->loger()->add(\LSYS\Loger::ERROR, $e);
        }
    }
    /**
     * swoole回调函数
     * @param Event $event
     */
    public function onFinish(Event $event) {
        $args=$event->data();
        if (!is_array($args))return ;
        $data=[];
        if(!isset($args[2])
            ||!$data=@json_decode($args[2],true)
            ||!is_array($data)
            ||!$obj=$this->deRuner($data))return ;
        try{
            $obj->onFinish($this->server,$args[0],$args[1],$data[2]??null);
        }catch (\Exception $e){
            \LSYS\Loger\DI::get()->loger()->add(\LSYS\Loger::ERROR, $e);
        }
    }
}