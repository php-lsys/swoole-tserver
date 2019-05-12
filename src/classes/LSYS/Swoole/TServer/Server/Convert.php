<?php
/**
 * @author     Lonely <shan.liu@msn.com>
 * @copyright  (c) 2017 Lonely <shan.liu@msn.com>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 */
namespace LSYS\Swoole\TServer\Server;
use LSYS\Swoole\TServer\Exception;
use LSYS\Swoole\TServer\Server\Convert\ThriftRender;
use LSYS\Swoole\TServer\Server\Convert\ArrayRender;
class Convert{
    /**
     * @var ThriftRender[]
     */
    protected $thrift_render;
    /**
     * @var ArrayRender[]
     */
    protected $array_render;
    /**
     * thrift转换转换工具
     * @param array $render
     */
    public function __construct(array $thrift_render=[],array $array_render=[]){
        $this->thrift_render=$thrift_render;
        $this->array_render=$array_render;
    }
    /**
     * 把数据转换为thrift个模型
     * @param mixed $data 数据
     * @param string $model 输出模型对象
     * @param array $map 映射关系
     * @param array $args 为具体的ConvertRender实现的构造函数的参数列表
     * @throws Exception
     * @return object
     */
    public function render($data,$model,array $map=[],array $args=null) {
        //开发阶段检测下,防止传错
        \LSYS\Core::$environment!=\LSYS\Core::PRODUCT&&assert(property_exists($model, '_TSPEC'));
        foreach ($this->thrift_render as $render) {
            if($render->renderMatch($data,$model,$args)){
                return $render->render($data, $model,$map,$args);
            }
        }
        throw new Exception("Not find model render");
    }
    /**
     * 把thrift模型对象转为数组
     * @param object $model_object
     * @param array $map
     * @param array $args
     * @return array|mixed
     */
    public function asArray($model_object,array $map=[],array $args=null) {
        \LSYS\Core::$environment!=\LSYS\Core::PRODUCT&&assert(property_exists($model_object, '_TSPEC'));
        foreach ($this->array_render as $render) {
            if($render->asArrayMatch($model_object,$args)){
                return $render->asArray($model_object,$map,$args);
            }
        }
        return $model_object;
    }
}