<?php
error_reporting(E_ALL);
$load=require dirname(__DIR__)."/vendor/autoload.php";
$load->addPsr4("","./app");
$load->addPsr4("","./msg");
$load->addPsr4("","./build");
$load->addPsr4("","./lib");
$load->addPsr4("","./task");
LSYS\Config\File::dirs(array(
    __DIR__."/config",
));