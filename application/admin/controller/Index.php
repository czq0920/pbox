<?php 
namespace app\admin\controller;
use app\common\controller\AdminBase;
use think\Model;

class Index extends AdminBase
 {
 	public function newCase()
 	{
		//$member = new Member();
		$data =Db('member') -> where('status=1')->order('order_date asc')->select();
		$data = $this->getCityName($data,2);
		//dump($data);die;
		$this->assign('data',$data);
 		return view();
 	}

	public function login()
 	{
 		return view();
 	}
	public function index()
	{
		return view();
	}
	public function choosePhotographer()
	{
		

	}
	public function  photographerList()
	{

		$photographer = db('photographer');
		$lists = $photographer -> select();
		//dump($lists);
		$this -> assign('lists',$lists);
		return $this->fetch('photographer');
	}
	public  function uploadTest()
	{
		return view();
	}
	public  function publish()
	{
		return view();
	}
	public  function addCase()
	{
		return view();
	}
	public  function editCase()
	{
		return view();
	}
	public  function followCase()
	{
		$data =Db('member') -> where('status=2')->order('order_date asc')->select();
		$data = $this->getCityName($data,2);
		//dump($data);die;
		$this->assign('data',$data);
		return view();
	}
	public  function overCase()
	{
		return view();
	}
	public  function addEvent()
	{

		$order_id = request()->param('order_id');
		
		$list = db('member')->field('order_id,event_title,order_date')->where('order_id=?',[$order_id])->find();
		if(!$list){
			$this->error('订单号不存在！','admin/index/newCase',3);
		}
		$this->assign('list',$list);
		return view();
	}
	public  function stopCase()
	{
		return view();
	}
	public  function orderCenter($option=1)
	{
		$lists_member =Db('member') ->field('order_id,order_date,city_number,event_title')-> where('status=?',[$option])->order('order_date asc')->select();
		//dump($lists_member);die;
		$this->assign('lists_member',$lists_member);
		return view();
	}
 }



 ?>
