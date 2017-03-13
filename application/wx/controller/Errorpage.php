<?php
namespace app\mice\controller;
use app\mice\model\User;
use think\Controller;
use isv\api\Auth;
class Errorpage extends BaseWeb
{
    public function index()
    {
    	/*$corpId =  intval($_GET['corpId']);
    	$user = User::get(["id"=>1])->toArray();
    	$config = Auth::isvConfig($corpId);
    	$config = '';
    	$this->assign(['config'=>$config]);
    	$this->assign(['corpId'=>$corpId]);*/
        $corpId = 'dingfb0d5dbeac2481b135c2f4657eb6378f';
        $config = Auth::isvConfig($corpId);
        $this->assign(['config'=>$config]);
        $this->assign(['corpId'=>$corpId]);
    	return $this->fetch('index');
    }
}


