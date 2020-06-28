<?php
/**
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\Swoole\TServer\Server\Convert\ThriftRender;
use LSYS\Swoole\TServer\Server\Convert\ThriftRender;
use LSYS\Swoole\TServer\Server\Convert\TraitArrayConvert;
class ArrRender implements ThriftRender
{
    /**
     * 键名驼峰转换为下划线
     */
    const underCase=1;
    /**
     * 键名转换为大写
     */
    const upperCase=2;
    /**
     * 键名转换为小写
     */
    const lowerCase=3;
    /**
     * 键名下划线转换为驼峰
     */
    const camelCase=4;
    use TraitArrayConvert;
    public function renderMatch($data,$model,array $args=null)
    {
        return is_array($data);
    }
    public function render($data, $model,array $map=[],array $args=null)
    {
        assert(is_array($data));
        if(is_array($args))call_user_func_array([__CLASS__,'__construct'],$args);
        $arr=$this->arrayChange($data, $map);
        return (new \ReflectionClass($model))->newInstance($arr);
    }
}
