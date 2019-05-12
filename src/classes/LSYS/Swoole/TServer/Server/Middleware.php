<?php
/**
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
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
