<?php
/**
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\Swoole\TServerTest;
use Thrift\ClassLoader\ThriftClassLoader;
use LSYS\Swoole\TServer\Server\Middleware\TokenMiddleware;
class ThriftLoader
{
    protected $loader;
    public function __construct(){
        $this->loader=new ThriftClassLoader();
    }
    public function addServer($server){
        if(!class_exists($server)&&is_subclass_of($server, LocalServer::class))return $this;
        call_user_func([$server,'autoload'], $this->loader);
        return $this;
    }
    public function middlewareLoader(){
        TokenMiddleware::registerDefinition($this->loader);
        return $this;
    }
    public function setDefinition($dir,$de){
        $de=(array)$de;
        foreach ($de as $v){
            $this->loader->registerDefinition($v,$dir);
        }
        return $this;
    }
    public function autoload() {
        return $this->loader->register();
    }
}