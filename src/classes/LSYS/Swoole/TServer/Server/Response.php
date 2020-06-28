<?php
/**
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\Swoole\TServer\Server;
use Thrift\Exception\TException;
class Response
{
    protected $result;
    public function __construct($result) {
        $this->result=$result;
    }
    /**
     * 结果过滤回调
     * 如在中间件中进行结果更改
     * @param callable $filter
     * @return $this
     */
    public function filter(callable $filter){
        $this->result=call_user_func($filter,$this->result);
        return $this;
    }
    /**
     * 请求是否成功
     * @return boolean
     */
    public function isSucc(){
        return !$this->result instanceof TException;
    }
    /**
     * 得到返回结构
     * @return object
     */
    public function getResult(){
        return $this->result;
    }
}
