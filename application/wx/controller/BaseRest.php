<?php
namespace app\mice\controller;
use app\mice\model\User;
use think\controller\Rest;
use isv\api\Auth;
use think\Session;
use think\Cookie;
use think\Request;
use think\Controller;
use think\Response;

class BaseRest extends Controller 
{

    protected $beforeActionList = [
        'authentication' =>  ['except'=>'login,sendmsg,getcompare'],
    ];

    protected $json_return_format = [
        'code'=>1000,
        'msg'=>'请求成功！',
        'data'=>[]
    ];

    public function authentication()
    {
    }

    public function _initialize()
    {
        Request::instance()->filter('htmlspecialchars,strip_tags');
    }

    protected function response($data, $type = 'json', $code = 200)
    {
        return Response::create($data, $type)->code($code);
    }
}
