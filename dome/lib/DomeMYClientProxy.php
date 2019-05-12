<?php
use LSYS\Swoole\Thrift\ClientProxy;
use LSM\TokenParam;
class DomeMYClientProxy extends ClientProxy{
    /**
     * 代理实现
     * @param string $method
     * @param array $param_arr
     * @return mixed
     */
    public function __call($method,$param_arr) {
        $token=end($param_arr);
        if ($token instanceof TokenParam) {
            if(empty($token->ip))$token->ip=\LSYS\Web\Request::ip(true);//请先引入lsys/web包,或自行实现获取IP的方法
            $token->platform=$this->config["platform"]??'1';
            $token->version=$this->config["version"]??'0.0.1';
            $token->timestamps=time();
            $token->signature=self::sign($token->timestamps,$token->platform);
        }
        return parent::__call($method, $param_arr);
    }
    protected function sign($time,$platform='1'){
        $app=$this->config["app"]??"app1";
        $save_token=$this->config["token"]??"";
        return strtolower(md5(strtolower($app.$platform.$save_token.$time)));
    }
}