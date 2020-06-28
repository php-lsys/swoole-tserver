<?php
require __DIR__ . "/boot.php";
$loader = new \Thrift\ClassLoader\ThriftClassLoader();
$loader->registerDefinition('Information',  __DIR__.'/gen-php');
$loader->registerDefinition('LSM',  dirname(__DIR__).'/src/gen-php');
$loader->register();


$product=DomeMYDI::get()->product();
var_dump($product->test("dddd", new LSM\TokenParam()));

//$product->release();//建议手动调用,兼容连接池方式

// 原始方式
// $socket = new Thrift\Transport\TSocket("127.0.0.1", 9809);
// $transport = new Thrift\Transport\TFramedTransport($socket);
// //协议要跟服务器对上
// $protocol = new Thrift\Protocol\TBinaryProtocol($transport);
// $client = new Information\DomeProductClient($protocol);
// $transport->open();


// function sign($time,$platform='1'){
//     $app="app1";
//     $save_token="111111";
//     return strtolower(md5(strtolower($app.$platform.$save_token.$time)));
// }

// try{
// //清理限制
// //     $recv = $client->breakerClearRequestLimit(new TokenParam([
// //         'version'=>'1.0.0',
// //         'signature'=>sign(time()),
// //         'timestamps'=>time(),
// //         'platform'=>'1',
// //         'ip'=>'127.0.0.1',
// //     ]),'test');
    
//     $recv = $client->test("123",new TokenParam([
//     'version'=>'1.0.0',
//     'signature'=>sign(time(),"client1"),
//     'timestamps'=>time(),
//     'platform'=>'client1',
//     'ip'=>'127.0.0.1',
// ]));
// var_dump($recv);
// }catch (Exception $e){
//     print_r($e);
// }

// $transport->close();



