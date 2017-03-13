<?php
namespace app\common\controller;


use think\Session;
use think\Request;
use think\Url;


// use 引入的类文件需要首字母大写

/**
* 基础类
*/
class AdminBase extends Common
{
	protected $request;
	private $module_name;

	function __construct()
	{
		parent::__construct();
		$this->request = Request::instance();
		if(Request::instance()->action() =="login")  return;
		$this->module_name = Request::instance()->module();
		if($this->module_name == "admin"){
			$this->checkAccess();
		}	
	 	//获取session
	 	//$userId = Session::get(config('USER_AUTH_KEY').'.id','admin');
		$userId = session('user_id');
	 	if(is_null($userId)){
	 		$this->goLogin();
	 	}
	}


	public function checkAccess()
	{
		$username = $this->key();

		if(is_null($username)){
			$this->goLogin();
			return false;
		}
	}

	public function key()
	{
		$user = session('username');
		if(is_null($user)){
			$this->goLogin();
		}
		if(empty($user)){
			$this->goLogin();
		}
		//$user = reset($user);
		return $user;
	}

	public function goLogin()
	{
		Session::clear();
		$redirect = '/admin/index/login';
		$this->redirect(Url::build($redirect));
	}


		/**
		 * 取消模板布局
		 */
	// protected function fetch($template = '', $vars = [], $replace = [], $config = [])
	// {
	// 	if (request()->isPost() || request()->isAjax()) {
	// 		// Config::set('layout_on',false);
	// 		// config(['layout_on'] => false);
	// 		// {__NOLAYOUT__}
	// 	}
	// 	// var_dump(321);die;
	// 	parent::fetch($template, $vars, $replace, $config);
	// 	exit();
	// }








}

