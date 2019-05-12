<?php
namespace TestServer;
use LSYS\Swoole\TServer\Server\Convert;
use LSYS\DI\SingletonCallback;
use LSYS\Swoole\TServerTest\LocalServer;
use TestFakeClient\PService;
class PServer extends LocalServer
{
    public static function autoload(\Thrift\ClassLoader\ThriftClassLoader $loader){
        //$loader->registerDefinition($namespace, $paths);
        return parent::autoload($loader);
    }
    public function handler()
    {
        return new \DomeMyHandler($this);
    }
    protected function bootstrap()
    {
        //MODEL 数据库注册,就是你的模型怎么去连接数据库
        \LSYS\Model\DI::set(function(){
            return (new \LSYS\Model\DI())
            ->modelDB(new SingletonCallback(function(){
                $db=\LSYS\Database\DI::get()->db("database.mysqli");
                return new \LSYS\Model\Database\Database($db);
            }));
        });
        //$this->thriftLoader()->register();
        $this->convert=new Convert([
            new \LSYS\Swoole\TServer\Server\Convert\ThriftRender\ArrRender(),//内置转换器 把数组转为thrift模型
            new \LSYS\Swoole\TServer\Server\Convert\ThriftRender\ModelRender(),//内置转换器 把数据库结果转为thrift模型
        ],
        [
            //内置转换器,把请求的thrift模型转换为数组
            new \LSYS\Swoole\TServer\Server\Convert\ArrayRender\ArrRender(),
        ]);
        
        \DomeMYDI::set(function(){
            return (new \DomeMYDI())
            ->product(new \LSYS\DI\MethodCallback(function(){
                return new PService();
            }));
        });
    }
    public function __destruct() {
        \DomeMYDI::set(NULL);
    }
}