<?php
namespace LSYS\Swoole\TServer\Server\Convert;
interface ArrayRender{
    /**
     * 模型转换为数组实现
     * @param object $model_object
     * @param array $args 调用方保证此参数为__construct函数一致
     */
    public function asArrayMatch($model_object,array $args=null);
    /**
     * 转数组匹配
     * @param object $model_object
     * @param array $args
     * @return mixed|array 调用方保证此参数为__construct函数一致 为NULL时候不进行__construct覆盖调用
     */
    public function asArray($model_object,array $map=[],array $args=null);
    
}