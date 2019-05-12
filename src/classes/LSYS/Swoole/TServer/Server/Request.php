<?php
namespace LSYS\Swoole\TServer\Server;
class Request implements \JsonSerializable
{
    protected $if;
    protected $method;
    protected $args=[];
    protected $nameArgs;
    public function __construct($if,$method,array $args){
        $this->if=$if;
        $this->setMethod($method);
        $this->setParameters($args);
    }
    /**
     * 获取当前服务处理对象
     * @return object
     */
    public function getHandler(){
        return $this->if;
    }
    /**
     * 设置方法
     * 可在中间件修改此值实现方法重定向
     * @param string $method
     * @return \LSYS\Swoole\TServer\Server\Request
     */
    public function setMethod($method){
        $this->method=$method;
        return $this;
    }
    /**
     * 设置请求的参数
     * 可在中间件中修改此值
     * @param array $args
     * @return \LSYS\Swoole\TServer\Server\Request
     */
    public function setParameters(array $args){
        $this->args=$args;
        return $this;
    }
    /**
     * 通过偏移得到参数
     * @param int $offset 偏移 负数从后开始获取
     * @param mixed $default 默认值
     * @return string
     */
    public function offsetParameter($offset,$default=null){
        if ($offset<=0) {$offset=count($this->args)+$offset;}
        if (isset($this->args[$offset])) {
            return $this->args[$offset];
        }
        return $default;
    }
    /**
     * 通过请求参数名得到参数
     * @param string $name
     * @param mixed $default
     * @return string
     */
    public function nameParameter($name,$default=null){
        if(!is_array($this->nameArgs)){
            $this->nameArgs=[];
            foreach ((new \ReflectionMethod($this->if,$this->getMethod()))->getParameters() as $v){
                $this->nameArgs[$v->getName()]=$v->getPosition();
            }
        }
        if (isset($this->nameArgs[$name])&&isset($this->args[$this->nameArgs[$name]])) {
            return $this->args[$this->nameArgs[$name]];
        }
        return $default;
    }
    /**
     * 得到当前请求的方法
     * @return string
     */
    public function getMethod(){
        return $this->method;
    }
    /**
     * 得到当前请求的参数
     * @return array
     */
    public function getParameters(){
        return $this->args;
    }
    private function argAsArray($arg){
        if (is_object($arg)) {
            $arg=get_object_vars($arg);
            foreach ($arg as $key=>$value) {
                if(is_object($value)){
                    $arg[$key]=$this->argAsArray($value);
                }else $arg[$key]=$value;
            }
        }
        return $arg;
    }
    /**
     * JSON序列化
     * 方便请求时记录,如记录请求日志
     * {@inheritDoc}
     * @see \JsonSerializable::jsonSerialize()
     */
    public function jsonSerialize()
    {
        return array(
            $this->method,
            array_map([$this,'argAsArray'],$this->args)
        );
    }
}