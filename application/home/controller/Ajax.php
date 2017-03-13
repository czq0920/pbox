<?php 
namespace  app\home\controller;
use app\common\controller\Common;
use app\home\model\OrderStatus;
use think\Loader;
use app\home\model\Member;
use app\home\model\Photographer;
use app\home\model\City;
class Ajax extends Common
{

    /**
     * 摄影师注册
     */
    function registerPhotographer()
    {
        
    	if(request()->ispost()){
    		$data = request()->param();
	 		//数据验证
	 		$validate = Loader::validate('Photographer');
            $result = $validate->check($data);
            //返回验证错误信息
            if(!$result){
                //判断是否是不符合自定义函数，否则返回系统验证信息
                $errorInfo = $validate->showError()?$validate->showError():$validate->getError();
                echo $errorInfo;die;
                //return  json_encode(['status'=>0,'msg'=>$errorInfo]);
            }
/*@TODO 短信验证
            if(time()-session('grapherRestCode')>300){
                return  json_encode(['status'=>0,'msg'=>'手机验证码超时！']);
            }
*/
            //上传图片及处理
            $thead = $this -> uploadImage('thead','photographer');
            //返回错误信息
            if(!$thead)
            {
                return json_encode(['status'=>0,'msg'=>$this->error]);
            }
	 		//入库 
	 		$photographer = new Photographer();
            $photographer -> username = $data['username'];
            $photographer -> realname = $data['realname'];
            $photographer -> mobile = $data['mobile'];
            $photographer -> sex = $data['sex'];
            $photographer -> work_address = $data['work_address'];
            $photographer -> skills = $data['skills'];
            $photographer -> thead = $thead;
            $res = $photographer -> save();	
            if($res)
            {
                //$this->redirect('index/index', '', 5, '注册成功！');
                return  json_encode(['status'=>1,'msg'=>'注册成功！']);
            }else{
                return  json_encode(['status'=>0,'msg'=>$photographer->getError()]);
            }
        }else{
            echo "不允许直接访问！";
            return $this->fetch('index/index');
        }	

        
    }

    /**
     * 发送短信
     * @return bool
     */
    public function checkCode()
    {
        if(request()->isajax()){
            $data = request()->param();
            $code = rand(1000,9999);
            $res = $this -> restValidate($data['mobile'],[$code,'5'],1);
            if(!$res){
                return false;
            }
            session('restCode',$code);
            session('restTime',time());
            return true;
        }
    }
    function orderHandle()
    {
        Db::transaction(function(){
        Db::table('think_user')->find(1);
        Db::table('think_user')->delete(1);
        });
    }
    /**
     * 用户提交订单
     * @return [type] [description]
     */
    public  function memberOrder()
    {
        if(request()->ispost()){
            $data = request()->param();
            //数据验证
            $validate = Loader::validate('Member');
            if(!$validate->check($data)){
                    //判断是否是不符合自定义函数，否则返回系统验证信息
                    $errorInfo = $validate->showError()?$validate->showError():$validate->getError();
                    return  json_encode(['status'=>0,'msg'=>$errorInfo]);

            }
            //判断验证码是否过期
            if(session('?grapherRestCode')||time()-session('grapherRestCode')>300){
                return  json_encode(['status'=>0,'msg'=>'手机验证码超时！']);
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
            $orderNumber = $this ->orderNumber($orderNumber_option);
            $member->order_id = $orderNumber;
            $member -> realname = $data['realname'];
            $member -> mobile = $data['mobile'];
            $member -> sex = $data['sex'];
            $member -> city_number = $data['city_number'];
            $member -> order_date = $data['order_date'];
            $member -> order_des = $data['order_des'];
            $order_status = new OrderStatus();
            $order_status -> order_id = $orderNumber;
            $order_status -> status = 0;
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
    /**
     *文件上传
     */
    public function upload()
    {
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('image');
        // 移动到框架应用根目录/public/uploads/ 目录下
        $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
        if($info){
        // 成功上传后 获取上传信息
        // 输出 jpg
        echo $info->getExtension();
        // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
        echo $info->getSaveName();
        // 输出 42a79759f284b767dfcb2a0197904287.jpg
        echo $info->getFilename();
        }else{
        // 上传失败获取错误信息
        echo $file->getError();
        }
    }

    /**
     * 查看我的订单数据支持
     */
    public function  showOrder()
    {
        //@TODO if(request()->isajax)),考虑TP5是否自身有自身过滤机制

        $member = new Member();
        $data = $member->select();
       foreach($data as $index=>$value)
       {
           echo  $value;
       }

    }
    public function  test()
    {
        $thead = $this -> uploadImage('uploadName','albums',1);
        //返回错误信息

        if(!$thead){
            echo json_encode(['status'=>0,'msg'=>$this->error]);
        }else{
            echo  json_encode(['status'=>1,'msg'=>'oh!']);
        }
    }
    public function sendMess($mobile='',$type=''){
        if($mobile=''){
            $mobile = request()->param('mobile');
            $type = request()->param('sign');
        }
        //检验手机号的合法性
        file_put_contents('sendMess.html',$mobile.$type);
       $this->sendMessage($mobile,$type);
        return  json_encode(["status"=>1,"msg"=>"发送成功！".session('grapherRestCode')]);


    }
}
 ?>