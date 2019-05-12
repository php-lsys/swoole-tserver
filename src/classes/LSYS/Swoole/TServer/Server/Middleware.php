<?php
namespace LSYS\Swoole\TServer\Server;
interface Middleware
{
    /**
     * 中间件回调接口
     * @param Request $request
     * @param \Closure $next
     */
    public function handle(Request $request, \Closure $next);
}
