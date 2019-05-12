<?php
/**
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\Swoole\TServer;
interface MQServerProcess
{
    /**
     * MQ附带进程,启动MQ服务器时会启动
     * 目前只有redis时候需要额外进程进行辅助实现延迟队列
     */
    public static function attachProcess();
}