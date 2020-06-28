<?php
use LSYS\Swoole\TServer\Server\Swoole\TimerRuner;
use LSYS\Swoole\TServer\Server;
class DomeTimer implements TimerRuner
{
    public function exec(Server $server)
    {
        echo "task\n";
        $finish("ok");
    }
}