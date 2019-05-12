<?php
/**
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\Swoole\TServer\Server\Convert;
interface ThriftRender{
    /**
     * 转模型匹配
     * @param mixed $data 待转换数据
     * @param string $model 输出模型
     * @param array $args 调用方保证此参数为__construct函数一致
     */
    public function renderMatch($data,$model,array $args=null);
    /**
     * 数据转换模型实现
     * @param mixed $data 待转换数据
     * @param string $model 输出模型
     * @param array $map 数据映射
     * @param array $args 调用方保证此参数为__construct函数一致 为NULL时候不进行__construct覆盖调用
     */
    public function render($data,$model,array $map=[],array $args=null);
    
}