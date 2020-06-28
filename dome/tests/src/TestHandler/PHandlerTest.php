<?php
namespace TestHandler;
use LSM\TokenParam;
use PHPUnit\Framework\TestCase;
use TestServer\PServer;
final class PHandlerTest extends TestCase
{
    
    public function testTest(){
        //黑盒测试
        $data=\DomeMYDI::get()->product()->test();
        $this->assertTrue(!empty($data));
    }
    public function testTest2()
    {
        //白盒测试
        $ls=new PServer();
        $this->assertTrue(is_object($ls->handler()->test("1", new TokenParam())));
    }
}