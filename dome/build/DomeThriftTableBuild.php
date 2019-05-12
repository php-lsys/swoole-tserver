<?php
namespace Lib;
use LSYS\Model\Database\Swoole\MYSQL;
//通过数据库生成thrift文件类定义
class DomeThriftTableBuild extends \LSYS\Swoole\Thrift\Tools\TableThriftBuild{
    protected $_mysql;
    protected $_db;
    public function __construct(){
        $this->setSaveDir(dirname(dirname(__DIR__))."/dome/thrift")
            ->setNamespace(["php Table"])
        ;
        $this->_mysql=\LSYS\Swoole\Coroutine\DI::get()->swoole_mysql();
        $this->_db=new MYSQL(function(){
            return $this->_mysql;
        });
    }
    public function listTables()
    {
        $sql='SHOW TABLES';
        $out=[];
        foreach ($this->_db->query($sql) as $value) {
            $out[]=array_shift($value);
        }
        return $out;
    }
    public function ColumnName($column){
        //字段名,可自定义
        return $column;
    }
    public function tablePrefix(){
        $config=$this->_mysql->getConfig();
        return isset($config['table_prefix'])?$config['table_prefix']:"";
    }
    public function message($table,$msg){
        echo $table.$msg."\n";
    }
    /**
     * {@inheritDoc}
     * @see \LSYS\Swoole\Thrift\Tools\TableThriftBuild::listColumns()
     */
    public function listColumns($table)
    {
        $columnset=$this->_db->listColumns($table);
        $out=[];
        foreach ($columnset->columnSet() as $value) {
            assert($value instanceof \LSYS\Entity\Column);
            //[$name,$type,$is_null,$commect]
            $out[]=[$value->name(),$value->getType(),$value->isAllowNull(),$value->comment()];
        }
        return $out;
    }
}