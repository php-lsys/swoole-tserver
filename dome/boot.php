<?php
error_reporting(E_ALL);
$load=require dirname(__DIR__)."/vendor/autoload.php";
$load->addPsr4("","./app");
$load->addPsr4("","./msg");
$load->addPsr4("","./build");
$load->addPsr4("","./lib");
$load->addPsr4("","./task");
// $load->addPsr4("Model\\","./Model");
// //注册MODEL的数据库DI依赖
// \LSYS\Model\DI::set(function(){
//     return (new \LSYS\Model\DI())
//     ->modelDB(new LSYS\DI\SingletonCallback(function(){
//         //协程
// //         return new \LSYS\Model\Database\Swoole\MYSQL(function($mysql=null,$is_master=0){
// //             if($is_master){
// //                 //返回主库连接
// //                 return \LSYS\Swoole\Coroutine\DI::get()->swoole_mysql();
// //             }else{
// //                 //返回从库连接
// //                 return \LSYS\Swoole\Coroutine\DI::get()->swoole_mysql();
// //             }
// //         });
//             //协程连接池
//             $pool=\LSYS\Swoole\Coroutine\DI::get()->swoole_mysql_pool();
//             $mp=new \LSYS\Model\Database\Swoole\MYSQLPool($pool);
//             //$mp->queryConfig("master*", "read*"); //设置 主从库的配置,默认都是从master*上取连接
//             return $mp;
//     }));
// });
LSYS\Config\File::dirs(array(
    __DIR__."/config",
));