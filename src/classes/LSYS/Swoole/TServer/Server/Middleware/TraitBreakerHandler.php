<?php
/**
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\Swoole\TServer\Server\Middleware;
use LSM\TokenParam;
/**
 * 清理拦截的方法片段
 * 参数不同请自行实现
 */
trait TraitBreakerHandler
{
    /**
     * {@inheritDoc}
     * @see \LSM\LSMServiceIf::breakerClearRequestLimit()
     */
    public function breakerClearRequestLimit(TokenParam $token,$method) {
        foreach ($this->server->middleware() as $v){
            if($v instanceof BreakerMiddleware){
                $v->clearRequestLimit($token,$method);
            }
        }
    }
    /**
     * {@inheritDoc}
     * @see \LSM\LSMServiceIf::breakerClearIpLimit()
     */
    public function breakerClearIpLimit(TokenParam $token,$method) {
        foreach ($this->server->middleware() as $v){
            if($v instanceof BreakerMiddleware){
                $v->clearIpLimit($token,$method);
            }
        }
    }
    
}
