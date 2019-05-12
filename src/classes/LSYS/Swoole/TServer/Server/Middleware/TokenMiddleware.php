<?php
/**
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\Swoole\TServer\Server\Middleware;
use LSYS\Swoole\TServer\Server\Middleware;
use LSYS\Swoole\TServer\Server\Request;
use LSYS\Swoole\TServer\Server;
use LSM\TokenException;
use LSM\TokenParam;
class TokenMiddleware implements Middleware
{
    use TraitTokenParse;
    use TraitSkipMethod;
    protected $server;
    protected $token_finder;
    protected $timeout;
    protected $check_client;
    /**
     * token校验中间件
     * @param Server $server
     * @param callable $token_finder token获取函数
     * @param number $timeout TOKEN超时时间
     * @param number $pos 参数的位置 默认最后一个参数
     * @param string $name 参数为某个参数的下级时的字段名
     */
    public function __construct(Server $server,callable $token_finder,$timeout=3600,$pos=-1,$name=null){
        $this->pos=$pos;
        $this->name=$name;
        $this->server=$server;
        $this->token_finder=$token_finder;
        $this->timeout=$timeout;
    }
    public function handle(Request $request, \Closure $next){
        if($this->isSkipRequest($request))return $next($request);
        $token=$this->parseTokenParam($request);
        if($token){
            $_token=call_user_func($this->token_finder,$token->platform);
            $sign_token=null;
            if(!$this->checkToken($this->server->getServerBuilder()->configName(), $_token, $token, $this->timeout, $sign_token)){
                if (\LSYS\Core::$environment===\LSYS\Core::PRODUCT)$sign_token="";
                else $sign_token="[{$sign_token}:{$_token}]";
                $msg="sign is wrong ".$sign_token;
            }else{
                return $next($request);
            }
        }else {
            $msg="token not find";
        }
        throw new TokenException(array(
            'status'=>403,
            'message'=>$msg
        ));
    }
    /**
     * 签名生成
     * 当需要自定义签名方式时,请重写此方法
     * 返回可不区分大小写
     * @param string $app
     * @param string $save_token
     * @param string $client
     * @param string $time
     * @return string
     */
    protected function signToken($app,$save_token,$client,$time){
        return md5(strtolower($app.$client.$save_token.$time));
    }
    /**
     * 检测签名
     * @param string $app
     * @param string $save_token
     * @param TokenParam $token
     * @param string $timeout
     * @param string $sign_token
     * @return boolean
     */
    protected function checkToken($app,$save_token,TokenParam $token,$timeout,&$sign_token){
        if($token->timestamps+$this->timeout<time()){
            return false;
        }
        $sign_token=strtolower($this->signToken($app,$save_token,$token->platform,$token->timestamps));
        return $sign_token==strtolower($token->signature);
    }
}
