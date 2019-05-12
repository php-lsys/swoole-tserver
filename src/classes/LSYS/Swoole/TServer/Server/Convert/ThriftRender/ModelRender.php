<?php
namespace LSYS\Swoole\TServer\Server\Convert\ThriftRender;
use LSYS\Swoole\TServer\Server\Convert\ThriftRender;
/**
 * 把entity对象或资源转换为thrift模型 
 */
class ModelRender extends ArrRender implements ThriftRender
{
    public function renderMatch($data,$model,array $args=null)
    {
        if(is_array($data)&&!class_exists(\LSYS\Entity\EntitySet::class)&&!class_exists(\LSYS\Entity::class))return false;
        return $data instanceof \LSYS\Entity||$data instanceof \LSYS\Entity\EntitySet;
    }
    public function render($data, $model,array $map=[],array $args=null)
    {
        if ($data instanceof \LSYS\Entity) {
            return parent::render($data->asArray(),$model,$map,$args);
        }else if($data instanceof \LSYS\Entity\EntitySet){
            $out=array();
            foreach ($data as $v){
                $out[]=self::render($v, $model,$map,$args);
            }
            return $out;
        }
    }
}
