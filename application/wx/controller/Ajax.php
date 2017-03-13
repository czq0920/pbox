<?php
namespace app\wx\controller;
class Ajax extends controller
{
    public function test()
    {
        echo 666;die;
        PDO_WX::getWechat()->test();
    }

    public function initMenu()
    {
        
    }
   
}