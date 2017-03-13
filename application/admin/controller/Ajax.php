<?php 
namespace app\admin\controller;
use app\admin\model\EventInfo;
use app\admin\model\OrderAlbum;
use app\admin\model\OrderInfo;
use app\common\controller\Common;
use app\home\model\Member;
use app\home\model\OrderStatus;
use think\Controller;
use app\admin\model\User;
use think\Db;
use think\Loader;
use app\home\model\Photographer;
class ajax extends Common
{
	private $order_id;
	private $user_id;
	public function orderStart()
	{

		//
		if(request()->isget()){
			$data = request()->param();
			//dump(session('user_id'));die;
			//1.记录订单信息
			$orderInfo = new OrderInfo();
			$orderInfo->order_id = $data['order_id'];
			$orderInfo -> user_id = session('user_id');
			$orderInfo ->save();
			$orderStatus = new OrderStatus();
			//2.记录订单状态
			$orderStatus -> order_id = $data['order_id'];
			$orderStatus -> status = 1;
			$orderStatus -> status_msg = "已分配拍摄专员处理！";
			$res = $orderStatus -> save();
			//3.渲染订单状态视图数据
			//Db::table('think_user')->where('status',1)->select();
			//$status = $this -> orderStatus();
			//$this -> assign('status',$status);
			//4.展示摄影师资料
			$photographer = new Photographer();
			$lists = $photographer -> select();
			//$lists['order_id'] = $data['order_id'];
			//dump($lists);die;
			$this -> assign('order_id',$data['order_id']);
			$this -> assign('lists',$lists);
			//C('TOKEN_ON',false);//关闭令牌验证
			return $this->fetch('index/photographer');
				//$this->redirect('admin/index/choosePhotographer',['order_id'=>$data['order_id']]);

			//随机生成订单号
			//hp --rand() --time() -- md5
			/**
			 * 其中设计订单详情页专员可以关单（状态6，记录原因）,更换摄影师（记录原因)
			 */
			//点击接单改变订单处理状态（1.已接单）
			//展示选择摄影师列表（根据城市优先排序）
			//专员与摄影师沟通，改变订单状态（2.正在安排摄影师）
			//与客户确认（3.与客户确认中）
			//通知摄影师结果（4.与摄影师确认中）
			//结果(5.完成)			
		}
	}

	/**
	 * @param int $status   case状态（订单） 查询该状态下各个case对应的城市，日期，等信息
	 * @return mixed
	 */
	public function orderStatus($status=0)
	{
		/**
		 * case 0://成功提交需求(未接单)
		//
		case 1://已安排摄影专员处理（处理中)
		//
		case 2://已安排摄影师（进行中）
		//
		case 3://活动举办（待结清）
		//
		case 4://拍摄文件已经提交（已结束）
		 */
		//命名绑定
		if(request()->isajax()){
			$data = request()->param();
			$status = intval($data['status']);
			$data = Db::query("select order_id,host_city,order_date from member  where order_id  in
				(select order_id  from  order_status where   status=?)", [$status]);
			if(!data){
				return json_encode(['status' => 0,'msg'=> "该case未录入信息！"]);
			}
			//@TODO 是否设计根据城市代码获得该城市名返回给前台
			foreach ($data as $list){
				$list['city_name'] =Db('city')->field('name')->where('c_number=?',[$list['host_city']])->find();
				$data_list[] = $list;
			}
			//dump($data_list);
			return json_encode(['status' => 1,'msg'=> $data_list]);
		}else{
			return json_encode(['status' => 0,'msg'=> '请求错误'] );
		}

	}
	public function addOrder()
	{
		if(request()->isajax()){
			$order = Order::get();
		}
	}


	/**后台用户登录验证
	 * @return mixed
	 */
	public function login()
	{
		$data = request()->param();
		//数据验证
		$validate = Loader::validate('User');
		if(!$validate->check($data)){

			return  $validate->getError();

		}
		//数据过滤
		$date = request()->param();
		$user = new User();
		//$user -> username = $date['username'];
		$lists = $user->where('username',$date['username'])->where('password',md5($date['password']))->find();
		if($lists)
		{
			session('user_id',$lists['id']);
			session('username',$date['username']);
			session('realname',$lists['realname']);
			//dump(session('username','','admin'));die;
			$this->redirect('admin/index/index');
		}
	}
	/**
	 * 选定摄影师
	 */

	public function  bindGrapher()
	{
		if(request()->isajax())
		{
			//接收订单号与摄影师ID
			$data = request()->param();
			$orderGrapher = db('order_grapher');
			$orderGrapher -> order_id = $data['order_id'];
			$orderGrapher -> prapher = $data['prapher_id'];
			$orderGrapher -> level = $data['level'];//初选0；确认：1
			$res = $orderGrapher -> save;
			if(!$res){
				return  json_encode(['status'=>0,'message'=>'选定摄影师失败！']);
			}
			return  json_encode(['status'=>1,'message'=>'成功选定摄影师！']);
		}
	}
	//图片处理系统
	/**按照开始时间和结束时间查询图片接口
	 * @return 照片集
	 */
	public function pictureHandle()
	{
		if(request()->isajax())
		{
			$data = request()->param();
			$startTime = strtotime($data['startTime']);
			$endTime = strtotime($data['endTime']);
			$album = db('order_album')->field(true)->where('is_cover=1 and create_time between ? and ?',[$startTime,$endTime])->order('update_time desc')->select();
			if(!$album)
			{
				return json_encode(['status'=>0,'message'=>"无符合条件图片信息！"]);
			}
			return json_encode(['status'=>1,'message'=>$album]);
		}
	}
	//订单下的详情图片信息（按照订单查询获得该订单下的图片集）
	public function pictureDetails()
	{
		if(request()->isajax()) {
			$order_id = request()->param('order_id');
			$album = db('order_album')->field(true)->where('order_id=?',$order_id)->order('update_time desc')->select();
			if(!$album)
			{
				return json_encode(['status'=>0,'message'=>"无该订单图片信息！"]);
			}
			return json_encode(['status'=>1,'message'=>$album]);
		}
	}

	/**
	 * 图片下载接口（响应c#图片下载请求）
	 */
	public function downPhoto()
	{
		//任何有关从服务器下载的文件操作，必然需要先在服务端将文件读入内存当中


		//file_put_contents('get.log',"flag".$order_id,FILE_APPEND );
		//判断请求方式，get为请求下载，post为请求上传。
		if(request()->isGet())
		{
			$order_id = request()->param('order_id');
			//$order_id = request()->param('order_id');
			//file_put_contents('order_id.log',$order_id,FILE_APPEND );
			//$user_id = intval(request()->param('user_id'));
			//$arr = $this->foreachDir($order_id);
			//获取该订单下的所有照片
			//select   photo_name from  order_album where  order_id=? and  status=0 and is_upload=0  and  is_down=0
			$album = db('order_album')->field('photo_name')->where('order_id=? and  status=0 and is_upload=0  and  is_down=0',[$order_id])->order('update_time desc')->select();
			$arr = [];
			foreach($album as $photoNames)
			{
				$suffix = strtolower(strrchr($photoNames['photo_name'], '.'));
				if(in_array($suffix, ['.jpg','.png','.gif','.jpeg']))
				{

					if(Db::execute("update order_album set is_down=1 where photo_name=?",[$photoNames['photo_name']]))
					{
						$arr[] = 'http://www.pbox.com/uploads/albums/'.$order_id.'/'.$photoNames['photo_name'];
					}
				}
			}
			if(!$arr){
				//该订单下没有需要下载的图片
				echo json_encode(['status'=>0]);
				exit;
			}
			//dump($arr);die;
			//@TODO 当该订单已经关闭，发送status=2下达停止该订单下下载照片请求
			echo json_encode(['status'=>1,'photoAddress'=>$arr]);
		}
	}

	/**
	 * 图片上传接口（c#图片上传处理）
	 */
	public function   uploadPhoto()
	{

		if(request()->isPost())
		{
			$order_id = request()->param('order_id');
			//file_put_contents('post.log',"flag".$order_id,FILE_APPEND );
			$user_id = intval(request()->param('user_id'));
			$thead = $this -> uploadImage('uploadName','albums/'.$order_id,1);
			if(!$thead)
			{

				echo json_encode(['status'=>0,'msg'=>$this->error[0]]);
				exit;
			}
			//专员修改后上传照片入库
			$orderAlbum = new OrderAlbum();
			$orderAlbum -> order_id = $order_id;
			$orderAlbum -> photo_name = $thead;
			$orderAlbum -> is_upload = 1;
			$orderAlbum -> user_id = $user_id;
			$res = $orderAlbum ->save();
			if(!$res){
				echo json_encode(['status'=>0,'msg'=>'照片信息写入数据库失败！']);
				exit;
			}
			echo  json_encode(['status'=>1,'msg'=>'上传成功！']);
		}
	}

	/**
	 * c#应用程序专员登录处理
	 */
	public  function   userLogin()
	{
		$data = request()->param();
		$username = $data['username'];
		//$password = md5($data['password'].config('salt'));
		$password = md5($data['password']);
		$order_id = $data['order_id'];
		//首先验证专员用户名和密码
		$res = db('user')->where('username=? and password=?',[$username,$password])->find();
		//dump($res);die;
		if(!$res)
		{
			echo json_encode(["status"=>0]);
			exit;
		}

		//file_put_contents('aaa.log',"after1",FILE_APPEND );
		//判断订单是否存在，
		//两种方法
		//1.根据照片存放规则/album/订单号查找是否有该文件夹
		//2.去数据库中寻找
		$res1 = db('order_info')->where('order_id=?',[$order_id])->find();
		if(!$res1)
		{
			echo json_encode(["status"=>2]);
			exit;
		}
		$user_id = $res['id'];
		echo json_encode(["status"=>1,"data"=>["user_id"=>$user_id,"order_id"=>$order_id]]);
	}
	public function addCase()
	{
		if(request()->ispost()){
			$data = request()->param();
			//数据验证
			$validate = Loader::validate('Member');
			if(!$validate->check($data)){
				//判断是否是不符合自定义函数，否则返回系统验证信息
				$errorInfo = $validate->showError()?$validate->showError():$validate->getError();
				echo $errorInfo;die;
				return  json_encode(['status'=>0,'msg'=>$errorInfo]);

			}
			//入库
			$member = new Member();
			$orderNumber_option = 1;
			if(isset($data['wx_open_id']))
			{
				$member -> wx_open_id = $data['wx_open_id'];
				$orderNumber_option = 2;
			}
			//$photographer -> username = $data['username'];
			$orderNumber = $this ->orderNumber($orderNumber_option,2);
			$member->order_id = $orderNumber;
			$member -> realname = $data['realname'];
			$member -> mobile = $data['mobile'];
			$member -> sex = $data['sex'];
			$member -> city_number = $data['city_number'];
			$member -> event_title = $data['event_title'];
			$member -> order_date = $data['order_date'];
			$member -> order_des = $data['order_des'];
			$member -> user_id = session('user_id');
			$order_status = new OrderStatus();
			$order_status -> order_id = $orderNumber;
			$order_status -> status = 1;
			$order_status -> status_msg = "成功提交需求";
			$order_status -> save();
			$res = $member -> save();
			if($res){
				//@todo 以后如果AJAX请求，返回JSON。
				return  json_encode(['status'=>1,'msg'=>'提交成功！']);
			}
			//return $this->fetch('index/index');
		}else{
			echo "不允许直接访问！";
		}
	}
	public function addEvent()
	{
		if(request()->ispost()){
			$data = request()->param();
			//数据验证
			//dump(strtotime($data["event_start_time"]));die;
			$validate = Loader::validate('event_info');
			if(!$validate->check($data)){
				//判断是否是不符合自定义函数，否则返回系统验证信息
				$errorInfo = $validate->showError()?$validate->showError():$validate->getError();
				//return  json_encode(['status'=>0,'msg'=>$errorInfo]);
				$this->error($errorInfo,'admin/index/addEvent',['order_id'=>$data['order_id']]);
			}
			//入库
			$eventInfo = new EventInfo();
			$eventInfo->order_id = $data['order_id'];
			$eventInfo -> company_name = $data["company_name"];
			$eventInfo -> company_contact = $data ["company_contact"];
			$eventInfo -> company_contact_mobile = $data ["company_contact_mobile"];
			$eventInfo -> event_address = $data["event_address"];
			$eventInfo -> event_type = $data["event_type"];
			$eventInfo -> event_number = $data["event_number"];
			$eventInfo -> event_consult = $data["event_consult"];
			$eventInfo -> event_consult_mobile = $data["event_consult_mobile"];
			$eventInfo -> event_start_time = strtotime($data["event_start_time"]);
			$eventInfo -> event_stop_time = strtotime($data["event_stop_time"]);
			$eventInfo -> user_id = session('user_id');

			$member_status_res = Db('member')->where('order_id',$data['order_id'])->setField('status',2);
			if(!$member_status_res){
				file_put_contents('log/error/event_update-status.txt','时间：'.date('Y-m-d H:m:s').'订单号：'.$data['order_id']."\r\n",FILE_APPEND);
				$this->error('更新摄影状态出错！','admin/index/addEvent',['order_id'=>$data['order_id']]);
			}
			$order_status = new OrderStatus();
			$order_status -> order_id = $data['order_id'];
			$order_status -> status = 2;
			$order_status -> status_msg = "录入活动信息成功";
			$order_status -> user_id = session('user_id');
			$order_status_res = $order_status -> save();
			if(!$order_status_res)
			{
				file_put_contents('/log/error/event_update_order_status.txt','时间：'.date('Y-m-d H:m:s').'订单号：'.$data['order_id']."\r\n",FILE_APPEND);
				$this->error('更新摄影状态出错！','admin/index/addEvent',['order_id'=>$data['order_id']]);
			}
			$res = $eventInfo -> save();
			if(!$res){
				//return  json_encode(['status'=>1,'msg'=>'提交成功！']);
				$this->error('添加失败！','admin/index/addEvent',['order_id'=>$data['order_id']]);
			}
			$this->success('添加成功！','admin/index/newCase','',2);
			//return $this->fetch('index/index');
		}else{
			echo "不允许直接访问！";
		}
	}
	//处理
	function navOrder()
	{
		if(request() -> isajax()){
			$data = request()->param();
			$type = $data['type'];
			$lists =Db('member') -> where('status=?',[$type])->order('order_date asc')->select();
			if(!$data){
				return json_encode(['status'=>0]);
			}
			return json_encode(['status'=>1,'data'=>$lists]);
		}
	}
	function  leftOrderRequest()
	{
		if(request() -> isajax()) {
			$data = request()->param();
			$order_id = $data['order_id'];
			//$city_number = $data['city_number'];
			$member_list = Db('member') ->field(true)-> where('order_id=?',[$order_id])->find();
			$event_list = Db('event_info') ->field(true)-> where('order_id=?',[$order_id])->find();
			$grapher_list = Db('photographer')->field('id,thead,working_time,realname,mobile,low_sal')->
							where('work_address=? and isdel=0',[$member_list['city_number']])->select();
			$lists = ['status'=>1,'data'=>['member'=>$member_list,'event'=>$event_list,'grapher'=>$grapher_list]];
			if(!$member_list){
				$lists = ['status'=>0];
			}
			return  json_encode($lists);
		}
	}
}
 ?>