<?php
namespace app\home\controller;
use think\Controller;
class Index extends Controller
{
	/**
	 * 网站首页展示功能
	 * @return [type] [description]
	 */
	public function index()
	{
		return view();
	}
	public function indexPhotographer()
	{
		return view('photographer');
	}
	public function indexMember()
	{
		return view('member');
	}
	public function test()
	{
		return view("test");
	}
}
?>