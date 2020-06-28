<?php
class MYMsgDome implements MYIMessage 
{
    public function __construct($arg1,$arg2=1){
        var_dump(func_get_args());
    }
}