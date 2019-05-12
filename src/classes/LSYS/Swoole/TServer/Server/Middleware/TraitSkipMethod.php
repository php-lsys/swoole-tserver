<?php
/**
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\Swoole\TServer\Server\Middleware;
use LSYS\Swoole\TServer\Server\Request;

trait TraitSkipMethod
{
    /**
     * 跳过拦截方法
     * @var string[]
     */
    private $skip_method=[];
    /**
     * 设置跳过拦截的方法名
     * @param array $method
     * @param boolean $overwrite
     * @return \LSYS\Swoole\TServer\Server\Middleware\BreakerMiddleware
     */
    public function setSkipMethod(array $method,$overwrite=false){
        if($overwrite)$this->skip_method=$method;
        else $this->skip_method=array_merge($this->skip_method,$method);
        return $this;
    }
    /**
     * 检测指定请求是否跳过
     * @param string $method
     * @return boolean
     */
    public function isSkipRequest(Request $request) {
        return in_array($request->getMethod(),$this->skip_method);
    }
}