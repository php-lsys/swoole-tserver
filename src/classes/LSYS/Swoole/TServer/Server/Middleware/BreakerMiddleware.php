<?php
namespace LSYS\Swoole\TServer\Server\Middleware;
use LSYS\Swoole\TServer\Server;
use LSYS\Swoole\TServer\Server\Middleware;
use LSYS\Swoole\TServer\Server\Request;
use LSM\TokenParam;
use LSM\BreakerException;
class BreakerMiddleware implements Middleware 
{
    use TraitTokenParse;
    use TraitSkipMethod;
    protected $table;
    protected $request_rule=[];
    protected $ip_rule=[];
    protected $redis_creater;
    protected $redis_release;
    protected $redis_prefix;
    protected $redis_try;
    /**
     * 服务拦截中间件
     * @param Server $server 
     * @param number $size 客户端和method组成组合的总数
     * @param number $pos TokenParam 参数的位置 默认最后一个参数
     * @param string $name TokenParam 参数为某个参数的下级时的字段名
     */
    public function __construct(Server $server,$size=1024,$pos=-1,$name=null){
        $this->pos=$pos;
        $this->name=$name;
        $table = new \Swoole\Table($size);
        $table->column('time', \Swoole\Table::TYPE_INT, 4);
        $table->column('total', \Swoole\Table::TYPE_INT, 4);
        $table->create();
        $this->table=$table;
        $this->skip_method=['breakerClearRequestLimit','breakerClearIpLimit'];
    }
    /**
     * 设置请求连接的规则
     * $rules ['方法名'=>['持续时间'=>'请求总数']]
     * @param array $rules
     * @return \LSYS\Swoole\TServer\Server\Middleware\BreakerMiddleware
     */
    public function setRequestLimit(array $rules){
        foreach ($rules as $method=>$rule){
            if(is_array($rule))foreach ($rule as $time=>$total){
                assert(is_int($time));
                assert(is_int($total));
                $this->request_rule[$method][$time]=$total;
            }
        }
        ksort($this->request_rule[$method]);
        return $this;
    }
    /**
     * 设置请求IP的规则
     * $rules ['方法名'=>['持续时间'=>'请求总数']]
     * @param object $redis
     * @param array $rules
     * @param string $prefix
     * @param int $try redis连接失败尝试次数
     * @return \LSYS\Swoole\TServer\Server\Middleware\BreakerMiddleware
     */
    public function setIpLimit(callable $redis_creater,callable $redis_release,array $rules,$prefix="breaker:",$try=2){
        $this->redis_creater=$redis_creater;
        $this->redis_release=$redis_release;
        foreach ($rules as $method=>$rule){
            if(is_array($rule))foreach ($rule as $time=>$total){
                assert(is_int($time));
                assert(is_int($total));
                $this->ip_rule[$method][$time]=$total;
            }
        }
        $this->redis_prefix=$prefix;
        $this->redis_try=$try;
        return $this;
    }
    /**
     * 清理全局或指定方法的IP限制
     * @param Request $request
     * @param string $method
     * @return boolean
     */
    public function clearIpLimit(TokenParam $token,$method=''){
        if(!$this->redis_creater)return ;
        $redis=$this->getRedis();
        if(!$redis)return ;
        $redis->del($token->platform.$method);
        call_user_func($this->redis_release,$redis);
    }
    /**
     * 检测REDIS连接
     * @return \LSYS\Redis||\LSYS\Swoole\Coroutine\Redis
     */
    protected function getRedis() {
        $loop=0;
        while($loop++<$this->redis_try){
            $redis=call_user_func($this->redis_creater,$loop);
            if ($redis instanceof \LSYS\Redis) {
                if(!$redis->isConnected()){
                    try{
                        $redis->configConnect();
                    }catch (\Exception $e){
                        continue;
                    }
                }
                if (@$redis->ping()===false) {
                    call_user_func($this->redis_release,$redis);
                    continue;
                }
            }
            if ($redis instanceof \LSYS\Swoole\Coroutine\Redis) {
                if(@$redis->ping()===false){
                    try{
                        $redis->connectFromConfig();
                    }catch (\Exception $e){
                        continue;
                    }
                }
                if(@$redis->ping()===false){
                    call_user_func($this->redis_release,$redis);
                    continue;
                }
            }
            return $redis;
        }
    }
    /**
     * 清理全局或指定方法的请求限制
     * @param Request $request
     * @param string $method
     * @return boolean
     */
    public function clearRequestLimit(TokenParam $token=null,$method=''){
        return $this->table->del(($token?$token->platform:'').$method);
    }
    /**
     * 请求限制输出
     * @param string $type 限制类型
     * @param int $next_time 下次可用时间
     * @param Request $request 
     * @throws BreakerException
     */
    protected function limitRequst($type,$next_time,Request $request){
        throw new BreakerException(array(
            'status'=>403,
            'type'=>$type,
            'message'=>'request limit on ['.$request->getMethod()."]",
            'time'=>$next_time
        ));
    }
    /**
     * 检测请求限制
     * @param string $client
     * @param string $method
     * @return number
     */
    protected function checkRequest($client,$method){
        if (isset($this->request_rule[$method])) {
            $key=$client.$method;
            foreach ($this->request_rule[$method] as $time=>$total){//60 => 500
                if($this->table->exist($key)&&$this->table->get($key,'time')>time()){
                    if ($this->table->get($key,'total')>=$total) {
                        return $this->table->get($key,'time')-time();
                    }
                    $this->table->incr($key,'total');
                }else{
                    $this->table->set($key,['time'=>time()+$time,'total'=>1]);
                }
            }
        }
        return 0;
    }
    /**
     * 检测IP限制
     * @param TokenParam $token
     * @param string $method
     * @return number
     */
    protected function checkIp(TokenParam $token,$method=''){
        if(isset($this->ip_rule[$method])){
            $key=$this->redis_prefix.$token->ip.$method;
            $redis=$this->getRedis();
            if(!$redis)return 0;
            foreach ($this->ip_rule[$method] as $time=>$total){//60 => 500
                $num=intval($redis->get($key));
                if($num>=$total){
                    $ttl=$redis->ttl($key);
                    call_user_func($this->redis_release,$redis);
                    return $ttl;
                }
                $redis->incr($key);
                if (!$num)$redis->expire($key,$time);
            }
            call_user_func($this->redis_release,$redis);
        }
        return 0;
    }
    public function handle(Request $request, \Closure $next){
       if(!$this->isSkipRequest($request)){
            $token=$this->parseTokenParam($request);
            if(!$token){
                $time=$this->checkRequest('',$request->getMethod());//无法识别客户端.只做全局方法验证拦截
                if ($time>0)  $this->limitRequst('request',$time,$request);
            }else{//根据客户端请求拦截
                $time=$this->checkRequest($token->platform, '');
                if ($time>0)  $this->limitRequst('request',$time,$request);
                $time=$this->checkRequest($token->platform, $request->getMethod());
                if ($time>0)  $this->limitRequst('request',$time,$request);
                if($this->redis_creater&&!empty($token->ip)
                    &&!in_array(substr($token->ip, 0,4), ['127.','192.'])//内网IP
                    &&!in_array(substr($token->ip, 0,3), ['10.'])//内网IP
                ){
                    $time=$this->checkIp($token, '');
                    if ($time>0)  $this->limitRequst('ip',$time,$request);
                    $time=$this->checkIp($token, $request->getMethod());
                    if ($time>0)  $this->limitRequst('ip',$time,$request);
                }
            }
        }
        return $next($request);
    }
}
