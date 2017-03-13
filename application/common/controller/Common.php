<?php
namespace app\common\controller;
use app\common\common\HxeUtil;
use think\Controller;
use mailer\PHPMailer;
use think\Db;
use think\Image;


/**
* 公用的控制器，pc、app、微信各端不需要控制权限的控制器，必须继承该控制器
 *
*/
class Common extends Controller
{
    protected $error;
    //先上传图片再对图片进行压缩，并删除原来上传的图片
    public  function uploadImage($fileName,$item,$type=0,$x=100,$y=100)
    {
       // thead   config('upload_path').'photographer'
        $upload_path = config('upload_path').$item.'/';
        //echo $upload_path;die;
        $file = request()->file($fileName);
        if(!$file)
        {
            $file = $_FILES[$fileName];
            if(!$file){
                $this ->error = "请选择上传图片！";
                return false;
            }
        }
        // 移动到框架应用根目录/public/uploads/photographer 目录下
        //还有一个额外的自动验证规则是，如果上传的文件后缀是图像文件后缀，则会检查该文件是否是一个合法的图像文件。
        $info = $file->validate(config('validate_thumb'))->move($upload_path);
        //file_put_contents('thumb_error.log',"after",FILE_APPEND );
        if(!$info){
            $this ->error = $file -> getError();
            return false;
        }
        $avatar = str_replace('\\','/',$info->getSaveName());
        if(!$avatar)
        {
            $this ->error = "上传图片失败！";
            return false;
        }

        if($type)
        {
            return  $avatar;
        }
        //die($upload_path.$avatar);
        $image = Image::open($upload_path.$avatar);

        $thumb = $image->thumb($x,$y)->save($upload_path.$avatar);
        if(!$thumb)
        {
            @unlink($upload_path.$avatar);
            $this ->error = "生成头像失败，请重新上传！";
            return false;
        }
        //压缩后的图片名称，默认为模板目录/当前日期/图片名
       return $avatar;

    }
    //进行邮箱验证处理
    protected  function doMailer($subject,$content,$sendAddree)
    {
        //实例化对象
        $mail = new PHPMailer();
        $configMail = config('mail');
        //设置属性（配置服务器账号、密码等）
        //3.设置属性，告诉我们的服务器，谁跟谁发送邮件
        $mail -> IsSMTP();			   //告诉服务器使用smtp协议发送
        $mail -> SMTPAuth = true;		//开启SMTP授权
        $mail -> Host = $configMail['Host'];	//告诉我们的服务器使用163的smtp服务器发送
        $mail -> From = $configMail['From'];	//发送者的邮件地址（使用这个邮箱给别人发送邮件）

        $mail -> FromName = $configMail['FromName'];		//发送邮件的用户昵称
        $mail -> Username = $configMail['Username'];	//登录到163的邮箱的用户名
        $mail -> Password = $configMail['Password'];	//第三方登录的授权码，在邮箱里面设置

        //编辑发送的邮件内容
        $mail -> IsHTML(true);		//发送的内容使用html编写
        $mail -> CharSet = 'utf-8';		//设置发送内容的编码
        $mail -> Subject = $subject;	//设置邮件的主题、标题
        $mail -> MsgHTML($content);			//发送的邮件内容主体

        //告诉服务器接收人的邮件地址
        $mail -> AddAddress($sendAddree);

        //调用send方法，执行发送
        $result = $mail -> Send();

        if($result){
            echo 'ok';
            return true;
        }else{

            echo $mail -> ErrorInfo;
            return false;
        }
    }
    protected function sendMessage($mobile="18734923636",$type="grapher")
    {
        //短信通道
        //短信通道配置
        $oper_id = "ydsz";
        $oper_pass = "ydsz";
        $send_url  = "http://221.179.180.158:9007/QxtSms/QxtFirewall";

        //处理数据 并 发送短信
        $sign = isset($mes['sign']) ? $mes['sign'] : '【会小二拍网摄影师注册验证码(5分钟有效)】 ';
        $data = array();
        $data['OperID']     = $oper_id;
        $data['OperPass']   = $oper_pass;
        $data['SendTime']   = '';
        $data['ValidTime']  = '';
        $data['AppendID']   = '2828';
        $data['DesMobile']  = $mobile;
        $code = rand(100000,999999);
        $content    = $sign . $code;
        session($type.'RestCode',$code);
        $len = mb_strlen($content, 'utf-8');
        if ($len > 70) {
            $data['ContentType'] = 8;
        } else {
            $data['ContentType'] = 15;
        }
        $str = http_build_query($data);
        //这里转换成了gbk
        $content = iconv('utf-8', 'gbk', $content);
        $url = $send_url . '?' . $str . '&Content=' . urlencode($content);
        $r_content = HxeUtil::curl($url);
        if(empty($r_content)) {
            $save_data['status'] = 3; //请求接口失败
        }else{
            $xml = simplexml_load_string($r_content, null, LIBXML_NOCDATA);
            $arr = HxeUtil::obj2array($xml);
            if($arr['code']=='03'){
                $save_data['status'] = 2; //发送成功

            }else{
                $save_data['status'] = 4; //发送失败

            }
        }
    }
    /**
     * 获取城市列表
     * 参数可以为城市编号或者包含城市编号的数组
     * 0:编号，1:1维数组，2:2维数组。
     */
    protected function getCityName($param,$type=0)
    {

        if($type==2){
            $arr=[];
            foreach($param as $list){
               $city_name = Db('city')->where('city_number=?',[$list['city_number']])->value('city_name');

                $list['city_name'] = $city_name;
                $arr[] = $list;
            }

            return $arr;
        }elseif ($type==1){
            $city_name = Db('city')->where('city=?',[$param['city_number']])->value('city_name');
            $param['city_name'] = $city_name;
            return $param;
        }elseif($type==0){
            $city_name = Db('city')->where('city=?',$param)->value('city_name');
            return $city_name;
        }else{
            return false;
        }
    }


    /**生成随机订单号规则
     * 下单渠道1位+支付渠道1位+业务类型1位+时间信息4位+下单时间的Unix时间戳后8位（加上随机码随机后的数字）
     * +用户user id后4位。19位
     * 下单渠道（1：网站，2微信）业务类型（1前台注册，2专员添加）   时间信息（月日4位） time()
     */
    protected function  orderNumber($orderWay=1,$type=1)
    {
        $orderNumber =   $orderWay.$type.date("md") .time().rand(1,9999);
        return  $orderNumber;
    }
}