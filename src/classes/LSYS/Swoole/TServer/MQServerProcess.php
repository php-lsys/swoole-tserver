<?php
namespace LSYS\Swoole\TServer;
interface MQServerProcess
{
    /**
     * MQ附带进程,启动MQ服务器时会启动
     * 目前只有redis时候需要额外进程进行辅助实现延迟队列
     */
    public static function attachProcess();
}