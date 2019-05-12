<?php
use LSYS\MQS\Runer;
interface MYIMessage extends Runer
{
    public function __construct($arg1,$arg2=1);
}