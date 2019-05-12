<?php
namespace LSYS\Swoole\TServer\Server\Convert\ArrayRender;
use LSYS\Swoole\TServer\Server\Convert\ArrayRender;
use LSYS\Swoole\TServer\Server\Convert\TraitArrayConvert;
class ArrRender implements ArrayRender
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
    public function asArrayMatch($model_object, array $args = null)
    {
        return is_object($model_object);
    }
    public function asArray($model_object, array $map = [], array $args = null)
    {
        assert(is_object($model_object));
        if(is_array($args))call_user_func_array([__CLASS__,'__construct'],$args);
        $model_object=$this->argAsArray($model_object);
        return $this->arrayChange($model_object, $map);
    }
    private function argAsArray($arg){
        $arg=get_object_vars($arg);
        foreach ($arg as $key=>$value) {
            if(is_object($value)){
                $arg[$key]=$this->argAsArray($value);
            }else $arg[$key]=$value;
        }
        return $arg;
    }
}
