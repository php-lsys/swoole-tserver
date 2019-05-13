<?php
/**
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\Swoole\TServer\Server\Swoole;
use LSYS\Swoole\TServer\Server;
class TimerManager
{
    protected $server;
    protected $timer=[];
    public function __construct(Server $server) {
        $this->server=$server;
    }
    /**
     * 指定名称的定时器是否存在
     * @param string $name
     * @return boolean
     */
    public function nameExist($name) {
        return is_array($this->timer[$name])&&count($this->timer[$name])>0;
    }
    /**
     * 清除指定名称的定时器
     * @param string $name
     * @return int[]
     */
    public function nameClear($name) {
        if (!$this->nameExist($name))return [];
        $ids=[];
        foreach ($this->timer[$name] as $id){
            $ids[]=$this->server->swoole()->clearTimer($id);
        }
        return $ids;
    }
    /**
     * 启动指定名称的一次定时器
     * $timer_runer 当为回调函数时,跟swoole保持一致
     * $timer_runer 当为实现timerRuner的类名时,$param为类构造函数参数列表
     * @param string $name
     * @param int $msec
     * @param callable|string $timer_runer
     * @param mixed $param
     * @return int
     */
    public function nameAfter($name,$msec,$timer_runer,$param=null) {
        return $this->timer[$name][]=$this->after($msec, $timer_runer,$param);
    }
    /**
     * 启动指定名称的连续定时器
     * $timer_runer 当为回调函数时,跟swoole保持一致
     * $timer_runer 当为实现timerRuner的类名时,$param为类构造函数参数列表
     * @param string $name
     * @param int $msec
     * @param callable|string $timer_runer
     * @param mixed $param
     * @return int
     */
    public function nameTick($name,$msec,$timer_runer,$param=null) {
        return $this->timer[$name][]=$this->tick($msec, $timer_runer,$param);
    }
    /**
     * 启动定时器，对swoole定时器二次封装
     * $timer_runer 当为回调函数时,跟swoole保持一致
     * $timer_runer 当为实现timerRuner的类名时,$param为类构造函数参数列表 
     * @param int $msec
     * @param callable|string $timer_runer
     * @param mixed $param
     * @return int
     */
    public function tick($msec,$timer_runer,$param=null) {
        return call_user_func([$this->server->swoole(),'tick'],$msec,$timer_runer,$param);
    }
    /**
     * 启动一次定时器，对swoole定时器二次封装
     * $timer_runer 当为回调函数时,跟swoole保持一致
     * $timer_runer 当为实现timerRuner的类名时,$param为类构造函数参数列表
     * @param int $msec
     * @param callable|string $timer_runer
     * @param mixed $param
     * @return int
     */
    public function after($msec,$timer_runer,$param=null) {
        return call_user_func([$this->server->swoole(),'after'],$msec,$timer_runer,$param);
    }
    /**
     * 清除指定定时器
     * @param int $id
     * @return int
     */
    public function clear($id) {
        return $this->server->swoole()->clearTimer($id);
    }
    /**
     * 方法调用
     * @param int $msec
     * @param callable|string $timer_runer
     * @param mixed $param
     * @param callable $callback
     * @return mixed
     */
    protected function call($msec,$timer_runer,$param=null,callable $callback){
        if (is_callable($timer_runer)){
            return call_user_func($callback,$msec,$timer_runer,$param);
        }
        if(is_string($timer_runer)&&$timer_runer instanceof timerRuner){
            $obj=(new \ReflectionClass($timer_runer))->newInstanceArgs(is_array($param)?$param:[$param]);
            return call_user_func($callback,$msec,function()use($obj){
                return $obj->exec($this->server);
            });
        }
    }
}