<?php
use LSYS\Swoole\TServer\Server\Swoole\TaskRuner;
class DomeTask implements TaskRuner
{
    public function onTask(\Swoole\Server　 $serv, $task_id, $src_worker_id, $task_flags = 0, callable $finish)
    {
        echo "task\n";
        $finish("ok");
    }
    public function onFinish(\Swoole\Server $serv, $task_id, $data)
    {
        echo $data."\n";
    }
}