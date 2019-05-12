<?php
namespace LSYS\Swoole\TServer\Server\Swoole;
use LSYS\Swoole\TServer\Server;
interface TimerRuner
{
    /**
     * 具体的定期执行代码
     * 在worker进程中执行
     */
    abstract public function exec(Server $server);
}