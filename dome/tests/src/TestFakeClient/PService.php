<?php
namespace TestFakeClient;
use LSM\TokenParam;
use Information\DomeProductIf;
class PService implements DomeProductIf
{
    public function test($id, TokenParam $token)
    {
        return "1";
    }

    public function breakerClearIpLimit(TokenParam $token, $method)
    {}

    public function breakerClearRequestLimit(TokenParam $token, $method)
    {}
}