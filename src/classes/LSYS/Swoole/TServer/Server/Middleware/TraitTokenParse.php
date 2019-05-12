<?php
/**
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\Swoole\TServer\Server\Middleware;
use LSYS\Swoole\TServer\Server\Request;
use LSM\TokenParam;
trait TraitTokenParse
{
    /**
     * 注册thrift依赖
     * @param \Thrift\ClassLoader\ThriftClassLoader $loader
     */
    public static function registerDefinition(\Thrift\ClassLoader\ThriftClassLoader $loader) {
        $loader->registerDefinition("LSM",dirname(dirname(dirname(dirname(dirname(dirname(__DIR__))))))."/gen-php");
    }
    protected $pos=0;
    protected $name;
    /**
     * 解析签名参数
     * @param Request $request
     * @return \LSM\TokenParam|null
     */
    protected function parseTokenParam(Request $request){
        $param=$request->offsetParameter($this->pos);
        if(!is_object($param))return null;
        if (empty($this->name)) {
            $token=$param;
        }else{
            if(!property_exists($param, $this->name))return null;
            $token=$token=$param->{$this->name};
        }
        if(!$token instanceof TokenParam)return null;
        return $token;
    }
}
