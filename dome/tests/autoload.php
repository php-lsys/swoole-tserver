<?php
use LSYS\Swoole\TServerTest\ThriftLoader;
use TestFakeClient\PService;
use TestServer\PServer;
if (is_file(__DIR__ . '/../vendor/autoload.php')) {
    $loader=require __DIR__ . '/../vendor/autoload.php';
    $loader->addPsr4("","./src");
}else {
    echo 'Cannot find the vendor directory, have you executed composer install?' . PHP_EOL;
    echo 'See https://getcomposer.org to get Composer.' . PHP_EOL;
    exit(1);
}
(new ThriftLoader())
    ->middlewareLoader()
    ->setDefinition(dirname(__DIR__)."/bootstrap/gen-php", ['Information'])
    ->addServer(PServer::class)
    ->autoload();

LSYS\Config\File::dirs(array(
    __DIR__."/config",
    dirname(__DIR__)."/config",
),false);

