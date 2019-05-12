<?php
use LSYS\Swoole\TServer\Kernel;
use LSYS\Swoole\TServer\ServerBuilder;
require __DIR__ . "/boot.php";
(new Kernel())
    ->addServerBuilder(new ServerBuilder(DomeMyServer::class,"app1"))
    ->handle();


