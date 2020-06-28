<?php
/**
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\Swoole\TServer\Server\Swoole;
use LSYS\Swoole\TServer\Server;
interface TaskRuner
{
    /**
     * 任务执行代码
     * 当启用协程化任务后在worker中执行
     * 未启用协程花任务时在task进程中执行
     * @param Server $server
     * @param \Swoole\Server　 $swoole_serv
     * @param int $task_id
     * @param int $src_worker_id
     * @param number $task_flags
     * @param callable $finish
     */
    public function onTask(Server $server,\Swoole\Server $swoole_serv,$task_id,$src_worker_id,$task_flags=0,callable $finish);
    /**
     * 任务完成时执行代码,在worker中执行
     * $data 为ontask中$finish中调用时传入的参数
     * @param Server $server
     * @param \Swoole\Server　 $swoole_serv
     * @param int $task_id
     * @param mixed $data
     */
    public function onFinish(Server $server,\Swoole\Server $swoole_serv,$task_id, $data);
}