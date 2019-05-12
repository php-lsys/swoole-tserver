<?php
/**
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\Swoole\TServer\Server;
class HandlerProxy{
    /**
     * The application's middleware stack.
     * @var array
     */
    protected $middleware = [];
    protected $if;
    protected $server;
    public function __construct(\LSYS\Swoole\TServer\Server $server,Handler $if) {
        $this->if=$if;
        $this->server=$server;
    }
    /**
     * 添加中间件
     * @param Middleware $middleware
     * @return \LSYS\Swoole\TServer\Server\HandlerProxy
     */
    public function addMiddleware(Middleware $middleware){
        $this->middleware[]=$middleware;
        return $this;
    }
    /**
     * 代理实现
     * @param string $method
     * @param array $args
     * @return object
     */
    public function __call($method,$args){
        $resp=(new Pipeline())->send(new Request($this->if,$method,$args))
            ->through($this->middleware)
            ->then($this->dispatchToThrift());
        if (!$resp->isSucc()) throw $resp->getResult();
        return $resp->getResult();
    }
    protected function dispatchToThrift()
    {
        return function (Request $request) {
            $res=call_user_func_array([$this->if,$request->getMethod()], $request->getParameters());
            return new Response($res);
        };
    }
}