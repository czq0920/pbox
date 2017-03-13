<?php
namespace app\mice\controller;
use app\mice\model\User;
use think\Controller;
use isv\api\Auth;
use think\Session;
use think\Cookie;
use think\Request;

class BaseWeb extends Controller
{
    protected $beforeActionList = [
        'authentication'=>['except'=>'index,compare,redirecturl'],
    ];

    public function authentication()
    {
    }
}


