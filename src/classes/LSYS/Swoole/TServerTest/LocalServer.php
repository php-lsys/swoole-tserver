<?php
namespace LSYS\Swoole\TServerTest;
use LSYS\Swoole\TServer\Server;
use LSYS\Swoole\Exception;
abstract class LocalServer extends Server
{
    private static $loader;
    public static function autoload(\Thrift\ClassLoader\ThriftClassLoader $loader){
        self::$loader=$loader;
        return $loader;
    }
    public function __construct() {
        if(self::$loader)$this->thriftLoader(self::$loader);
        $this->bootstrap();
    }
    public function protocolFactory()
    {
        throw new Exception("local test server not support ".__METHOD__." method");
    }
    public function makeServer($swoole)
    {
        throw new Exception("local test server not support ".__METHOD__." method");
    }
    public function makeSwoole(array $config)
    {
        throw new Exception("local test server not support ".__METHOD__." method");
    }
    public function server(){
        throw new Exception("local test server not support ".__METHOD__." method");
    }
    public function swoole(){
        throw new Exception("local test server not support ".__METHOD__." method");
    }
    public function run()
    {
        throw new Exception("local test server not support ".__METHOD__." method");
    }
    protected function terminate($status){
        throw new Exception("local test server not support ".__METHOD__." method");
    }
    public function processor($handler){
        throw new Exception("local test server not support ".__METHOD__." method");
    }
    public function getServerBuilder(){
        throw new Exception("local test server not support ".__METHOD__." method");
    }
}