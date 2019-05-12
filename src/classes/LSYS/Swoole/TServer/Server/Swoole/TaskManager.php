<?php
namespace LSYS\Swoole\TServer\Server\Swoole;
use LSYS\Swoole\TServer\Server;
use LSYS\Swoole\Thrift\Server\SwooleSubject;
use LSYS\Swoole\Thrift\Server\SwooleEvent;
use LSYS\EventManager\CallbackObserver;
use LSYS\Swoole\TServer\Exception;
class TaskManager 
{
    protected $server;
    public function __construct(Server $server) {
        $this->server=$server;
        $this->server->getEventManager()->attach((new SwooleSubject(SwooleEvent::Task))->attach(new CallbackObserver([$this,"onTask"])));
        $this->server->getEventManager()->attach((new SwooleSubject(SwooleEvent::Finish))->attach(new CallbackObserver($this,"onFinish")));
    }
    /**
     * 发送任务
     * @param string $task_runer
     * @param array $data
     * @param int $dst_worker_id
     * @param callable $callback
     * @throws Exception
     * @return int|false
     */
    public function task($task_runer,array $data, $dst_worker_id=-1, $callback=null){
        if(!$task_runer instanceof TaskRuner){
            throw new Exception("task args need implements taskRuner");
        }
        return $this->server->swoole()->task([$task_runer,$data], $dst_worker_id, function(\Swoole\Server $serv, $task_id, $data)use($callback){
            if(!is_callable($callback)
                ||!$data=@json_decode($data,true)
                ||!is_array($data))return ;
            return call_user_func($callback,$serv,$task_id,$data[2]??null);
        });
    }
    /**
     * 发送同步任务
     * @param string $task_runer
     * @param array $data
     * @param int $dst_worker_id
     * @throws Exception
     * @return int|false
     */
    public function taskwait($task_runer,array $data, $dst_worker_id=-1){
        if(!$task_runer instanceof TaskRuner){
            throw new Exception("task args need implements taskRuner");
        }
        return $this->server->swoole()->taskwait([$task_runer,$data], $dst_worker_id);
    }
    /**
     * 执行批量任务
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
            assert(isset($v[0])&&$v[0] instanceof TaskRuner);
            assert(isset($v[1])&&is_array($v[1]));
        }
        return $this->server->swoole()->taskWaitMulti($tasks,$timeout);
    }
    /**
     * swoole回调函数
     * @param SwooleSubject $subject
     */
    public function onTask($subject) {
        if(\Swoole\Coroutine::getuid()==-1){
            call_user_func_array([$this,"onTask_"], $subject->event()->eventArgs());
        }else{
            call_user_func_array([$this,"onTaskCo_"], $subject->event()->eventArgs());
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
            $obj->onTask($this->server,$serv,$task_id,$src_worker_id,0,function($fdata)use($data,$serv){
                $task->finish(json_encode([get_class($obj),$data[1],$fdata],JSON_UNESCAPED_UNICODE));
            });
        }catch (\Exception $e){
            \LSYS\Loger\DI::get()->loger()->add(\LSYS\Loger::ERROR, $e);
        }
    }
    protected function onTaskCo_($serv, $task){
        $obj=$this->deRuner($task->data);
        if(!is_object($obj))return ;
        try{
            $obj->onTask($this->server,$serv,$task->id,$task->worker_id,$task->flags,function($fdata)use($task){
                $task->finish(json_encode([get_class($obj),$task->data[1],$fdata],JSON_UNESCAPED_UNICODE));
            });
        }catch (\Exception $e){
            \LSYS\Loger\DI::get()->loger()->add(\LSYS\Loger::ERROR, $e);
        }
    }
    /**
     * swoole回调函数
     * @param SwooleSubject $subject
     */
    public function onFinish($subject) {
        $args=$subject->event()->eventArgs();
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