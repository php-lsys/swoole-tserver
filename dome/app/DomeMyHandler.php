<?php
use LSYS\Swoole\TServer\Server\Handler;
use LSYS\Swoole\TServer\Server\Middleware\TraitBreakerHandler;
use Information\DomeProductIf;
class DomeMyHandler extends Handler implements DomeProductIf
{
    use TraitBreakerHandler;
    public function test($msg,\LSM\TokenParam $token)
    {
        //发送消息
        //var_dump(MYDI::get()->mq1()->send(MYIMessage::class,[uniqid(),uniqid()]));
//         $res=$this->convert->render([
//             'TTBpageSS'=>100,
//             'TTBpageCountSS'=>22,
//             'TTBcountSS'=>11111
//         ], \Information\DomeResultPage::class,[],[null,'TT','SS']);//赋值时取出前后缀
        $res=$this->convert->render([
            'page'=>100,
            'pageCount'=>22,//跟模型对不上的手动映射
            'count'=>11111
        ], \Information\DomeResultPage::class,['pageCountt'=>'pageCount'],[4,'','','B']);
        return $res;
    }
}