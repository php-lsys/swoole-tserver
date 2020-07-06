<?php
use LSYS\Model\Tools\TraitBuild;
use LSYS\Model\Database\Swoole\MYSQL;
//通过数据库生成模型实现类
class DomeModelBuild extends TraitBuild{
    protected $_db;
    public function __construct(){
        $this->setSaveDir(dirname(__DIR__))
            ->setNamespace("Model")
        ;
        $this->_mysql=\LSYS\Swoole\Coroutine\DI::get()->swoole_mysql("swoole.mysql_pool.master.connection");
        $this->_db=new MYSQL(function(){
            return $this->_mysql;
        });
    }
    public function db(){
        return $this->_db;
    }
    public function listTables():array
    {
        $sql='SHOW TABLES';
        $out=[];
        foreach ($this->_db->query($sql) as $value) {
            $out[]=array_shift($value);
        }
        return $out;
    }
    public function tablePrefix():string{
        return strval(\LSYS\Config\DI::get()->config("swoole.mysql_pool")->get("table_prefix"));
    }
    public function message(string $table,string $msg):void{
        echo $table.":".$msg."\n";
    }
}