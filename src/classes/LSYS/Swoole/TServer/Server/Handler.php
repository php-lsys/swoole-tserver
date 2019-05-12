<?php
namespace LSYS\Swoole\TServer\Server;
abstract class Handler{
    protected $server;
    protected $convert;
    /**
     * 基础的服务处理对象
     * 类似于MVC中C的基类
     * @param \LSYS\Swoole\TServer\Server $server
     */
    public function __construct(\LSYS\Swoole\TServer\Server $server) {
        $this->server=$server;
        $this->convert=$server->convert();
    }
}