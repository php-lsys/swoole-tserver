<?php
use LSYS\Swoole\TServer\Server\Middleware;
use LSYS\Swoole\TServer\Server\Request;
class DomeMyMiddleware implements Middleware
{
    public function handle(Request $request, \Closure $next)
    {
        //自定义中间件....
        $response=$next($request);
        if(!$response->issucc()){
            //进行资源释放....
        }
        return $response;
    }
}