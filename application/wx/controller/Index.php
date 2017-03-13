<?php
namespace  app\wx\controller;
use app\wx\common\Wechat;
use app\wx\model\WXMedia;
use think\Config;
use think\Controller;
use think\Db;
use think\Exception;
use think\exception\ErrorException;

class Index extends  Controller
{
	private $weObj;
	private static $Weobj_instance;

	public function __construct()
	{
		parent::__construct();
		$this -> weObj = new Wechat(config('options'));
		//$this->wxoauth();
	}

	private function __clone()
	{
		
	}

	//网页授权获取用户基本信息
	public function wxoauth()
	{
		//以snsapi_base为scope发起的网页授权，是用来获取进入页面的用户的openid的，
		//并且是静默授权并自动跳转到回调页的。用户感知的就是直接进入了回调页（往往是业务页面）
		$scope = 'snsapi_base';
		$code = isset($_GET['code'])?$_GET['code']:'';
		$token_time = isset($_SESSION['token_time'])?$_SESSION['token_time']:0;
		if(!$code && isset($_SESSION['open_id']) && isset($_SESSION['user_token']) && $token_time>time()-3600)
		{

			if (!$this->wxuser) {
				$this->wxuser = $_SESSION['wxuser'];
			}
			$this->open_id = $_SESSION['open_id'];
			return $this->open_id;
		}
		else
		{

			if ($code) {
				$json = $this->weObj->getOauthAccessToken();
				if (!$json) {
					unset($_SESSION['wx_redirect']);
					die('获取用户授权失败，请重新确认');
				}
				$_SESSION['open_id'] = $this->open_id = $json["openid"];
				$access_token = $json['access_token'];
				$_SESSION['user_token'] = $access_token;
				$_SESSION['token_time'] = time();
				//用户管理类接口中的“获取用户基本信息接口”，是在用户和公众号产生消息交互或关注后事件推送后，
				//才能根据用户OpenID来获取用户基本信息。这个接口，包括其他微信接口，都是需要该用户（即openid）关注了公众号后，才能调用成功的
				$userinfo = $this->weObj->getUserInfo($this->open_id);
				if ($userinfo && !empty($userinfo['nickname'])) {
					$this->wxuser = array(
						'open_id'=>$this->open_id,
						'nickname'=>$userinfo['nickname'],
						'sex'=>intval($userinfo['sex']),
						'location'=>$userinfo['province'].'-'.$userinfo['city'],
						'avatar'=>$userinfo['headimgurl']
					);
				} elseif (strstr($json['scope'],'snsapi_userinfo')!==false) {
					//以snsapi_userinfo为scope发起的网页授权，是用来获取用户的基本信息的。
					//但这种授权需要用户手动同意，并且由于用户同意过，所以无须关注，就可在授权后获取该用户的基本信息。
					$userinfo = $this->weObj->getOauthUserinfo($access_token,$this->open_id);
					if ($userinfo && !empty($userinfo['nickname'])) {
						$this->wxuser = array(
							'open_id'=>$this->open_id,
							'nickname'=>$userinfo['nickname'],
							'sex'=>intval($userinfo['sex']),
							'location'=>$userinfo['province'].'-'.$userinfo['city'],
							'avatar'=>$userinfo['headimgurl']
						);
					} else {
						return $this->open_id;
					}
				}
				if ($this->wxuser) {
					$_SESSION['wxuser'] = $this->wxuser;
					$_SESSION['open_id'] =  $json["openid"];
					unset($_SESSION['wx_redirect']);
					return $this->open_id;
				}
				$scope = 'snsapi_userinfo';
			}
			if ($scope=='snsapi_base') {
				$url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
				$_SESSION['wx_redirect'] = $url;
			} else {
				$url = $_SESSION['wx_redirect'];
			}
			if (!$url) {
				unset($_SESSION['wx_redirect']);
				die('获取用户授权失败');
			}
			$oauth_url = $this->weObj->getOauthRedirect($url,"wxbase",$scope);

			dump($oauth_url);die;
			header('Location: ' . $oauth_url);

		}
	}
	//日志重载
	function logdebug($text){
		file_put_contents('/wx/data/log.txt',$text."\n",FILE_APPEND);
	}
	public static function getWechat()
	{
		if (!self::$Weobj_instance instanceof self) {
			self::$Weobj_instance = new Index();
		}
		return self::$Weobj_instance;
	}

	public function index()
	{
		return view();
	}
	public function grapher()
	{
		return view();
	}
	public function member()
	{
		return view();
	}
	public function orderinfo()
	{
		return view();
	}
	public function about()
	{
		return view();
	}
	public function create_wx_menu()
	{
		//dump(WX_SITE_URL);die;
		$menu = '
       {
           "button": [
               {
                   "type": "view",
                   "name": "预约拍照",
                   "url": "'.WX_SITE_URL.'index/member"
               },
               {
                   "type": "view",
                   "name": "今日头图",
                   "url": "'.WX_SITE_URL.'index/grapher"
               },
                
               {
                   "name": "更多",
                   "sub_button":[
                        {
                           "type": "view",
                           "name": "订单中心",
                           "url": "'.WX_SITE_URL.'index/orderinfo"
						   },
						  
						   {
							   "type": "click",
							   "name": "咨询电话",
							   "key": "SERVICE_PHONE"
						   },
						   {
								"type" : "location_select",
								"name" : "发送位置",
								"key" : "rselfmenu_2_0"
							},
						   {
							   "type": "view",
							   "name": "小二俱乐部",
							   "url": "http://m.wsq.qq.com/262801914"
						   },
						   {
							   "type": "view",
							   "name": "使用说明",
							   "url": "'.WX_SITE_URL.'index/about"
						   }
                   ]
               }
           ]
       }';
		$res = $this -> weObj ->createMenu($menu);
		dump($res);
	}

	/**
	 * 获取用户open_Id
	 */
	public function  getRevFrom()
	{
		return $this ->weObj ->getRevFrom();
	}

	/**
	 * 获取用户信息
	 */
	public  function  getUserInfo($opend_id)
	{
		$res = $this->weObj->getUserInfo($opend_id);
		return $res;
	}
	/**
	 * 返回消息内容正文或语音识别结果（文本型）
	 */
	public function  getRevContent()
	{
		$res = $this ->weObj ->getRevContent();
		//file_put_contents('/log/getRevContent.html',$this ->weObj ->getRevFrom().$res.'<hr>',FILE_APPEND);
		return $res;
	}
	/**
	 * 添加客服账号
	 *
	 * @param string $account      //完整客服账号，格式为：账号前缀@公众号微信号，账号前缀最多10个字符，必须是英文或者数字字符
	 * @param string $nickname     //客服昵称，最长6个汉字或12个英文字符
	 * @param string $password     //客服账号明文登录密码，会自动加密
	 * @return boolean|array
	 * 成功返回结果
	 * {
	 *   "errcode": 0,
	 *   "errmsg": "ok",
	 * }
	 */
	public function  addKFAccount($account,$nickname,$password)
	{
		return $this ->weObj ->addKFAccount($account,$nickname,$password);
	}
	public function test()
	{
		//添加客服测试
		$account = 'czq@'.config('wx_sys_number');
		$nickname = '陈志强';
		$password = '123456';
		$res = $this -> addKFAccount($account,$nickname,$password);
		dump($account);

	}
	public function autoReply()
	{
		$this -> weObj->valid();//明文或兼容模式可以在接口验证通过后注释此句，但加密模式一定不能注释，否则会验证失败
		$type = $this -> weObj->getRev()->getRevType();
		switch ($type) {
			case Wechat::MSGTYPE_TEXT:
				$res = $this ->getRevContent();
				$this -> weObj->text($res)->reply();
				break;
			case Wechat::MSGTYPE_EVENT:
				file_put_contents('wx_event_log',$this->weObj->getRevEvent()['event'].'---'.$this->weObj->getRevEvent()['key'],FILE_APPEND);
				$this->doEvent($this->weObj->getRevEvent());
				//$this -> weObj->text('event test')->reply();
				break;
			case Wechat::MSGTYPE_IMAGE:
				$index = rand(0,10);
				$arr_image = ['Er1Tmp5Qt5c-S8Tf2XfXdqLqS01NSa61tdjjT1Fqfkk','Er1Tmp5Qt5c-S8Tf2XfXdpdCZ9cc2sW2QAt__W5MgiI',
					'Er1Tmp5Qt5c-S8Tf2XfXdruJ47GaYebocba02EdMXgI','Er1Tmp5Qt5c-S8Tf2XfXdgt4lKfkwB91BDCcoGoqI1I',
					'Er1Tmp5Qt5c-S8Tf2XfXdvhurvIFUBtFEP3My-Hy73M','Er1Tmp5Qt5c-S8Tf2XfXdjs4Qdq7Tu7cTHQco3vLe1A',
					'Er1Tmp5Qt5c-S8Tf2XfXdkMr2yy76hGvgRtwQuua3RQ','Er1Tmp5Qt5c-S8Tf2XfXdiSDS0UMyiT82OLhgfGZPk0',
					'Er1Tmp5Qt5c-S8Tf2XfXdnkS1g4CRHO2zCAMSh_HRAs','Er1Tmp5Qt5c-S8Tf2XfXdnkS1g4CRHO2zCAMSh_HRAs',
					'PznOWd7iP3fYLR7NzHLm-4SxoaeHc53L4-SMyiIPHMKy4Y-Ot3byy9JYo9ea79xl',
				];
				$this -> weObj->image($arr_image[$index])->reply();
				break;
			case Wechat::MSGTYPE_LOCATION:
				$this -> weObj->text("已收到您的实时位置")->reply();
				break;
			case Wechat::MSGTYPE_LINK:
				$this -> weObj->text("LINK test")->reply();
				break;
			case Wechat::MSGTYPE_MUSIC:
				$this -> weObj->text("MUSIC 请发送音乐名称")->reply();
				break;
			case Wechat::MSGTYPE_NEWS:
				$this -> weObj->text("NEWS test")->reply();
				break;
			case Wechat::MSGTYPE_VOICE:
				$this -> weObj->text("VOICE您可以联系我们的客服哦~")->reply();
				break;
			case Wechat::MSGTYPE_VIDEO:
				$this -> weObj->text("VIDEO占不支持视频聊天")->reply();
				break;
			default:
				$this -> weObj->text("不能识别您的消息，如有疑问请联系客服。18734923636")->reply();
		}
	}
	public  function displayPhoto()
	{
		//
		$lists = Db('order_album')->field(['id','order_id','photo_name','update_time'])->where('status=1')->order('update_time desc')->paginate(10);
		$this->assign('lists', $lists);
		return $this->fetch();
	}
	public function sendMsg()
	{
		//$res = $this -> weObj->getUserList();
		$res = $this -> weObj->sendMassMessage(
				array(
					'ToUserName'=>array('onm1jwPia20C0HYPSCbPtaHVdV0I','onm1jwAD3vu1V53eaygfOmcfADfU','onm1jwKM7L0Agos_3J00yg7jL5ok'),
					'FromUserName'=>'onm1jwAD3vu1V53eaygfOmcfADfU',
					'CreateTime'=>'1486378084',
					'MsgType' =>'text',
					'Content'=>'在干吗呢'
				));
		dump($res);
	}
	function foreachDir($order_id='11011114841131936682')
	{
		$root_path = config('upload_path').'albums/'.$order_id.'/20170206/';
		$arr = [];
		//die($root_path);
		if(!is_dir($root_path))
		{
			die('所选目录不存在！');

		}

		$dir = opendir($root_path);
		// var_dump(readdir($dir));
		// die;
		while(($filename = readdir($dir))!==false)
		{
			//$filename = iconv('gb2312','utf-8',$filename);
			if(is_dir($root_path.'/'.$filename))
			{
				//echo '目录名：'.$filename.'<br>';
				$tmp_dir = $root_path.'/'.$filename;
				if($filename!='.'&&$filename!='..')
				{
					$this -> foreachDir($tmp_dir);
				}
			}
			else
			{
				//$filename = iconv('gb2312','utf-8',$filename);
				$suffix = strtolower(strrchr($filename, '.'));
				if(in_array($suffix, ['.jpg','.png','.gif','.jpeg']))
				{
					//$arr= array("media"=> 'http://www.pbox.com/uploads/albums'.$order_id.'/'.$filename);
					$arr["media"]= '@'.Config('upload_path').'albums/'.$order_id.'/20170206/'.$filename;
					try{
						$res = $this -> uploadMedia($arr,'image',$filename,0);
					}catch (Exception $e){
						file_put_contents('img_media_id_error',$filename.'\n',FILE_APPEND);
						continue;
					}


					file_put_contents('img_media_id',$filename.$res.'\n',FILE_APPEND);
				}
			}
		}
		closedir($dir);
		return $arr;

	}

	/**
	 * @param $data上传数据，可以为json可以为数组
	 * @param $type 媒体文件类型，分别有图片（image）、语音（voice）、视频（video）和缩略图（thumb，主要用于视频与音乐格式的缩略图）
	 * @param int $is_temporary是否为临时素材，1为临时素材
	 * 注意：上传大文件时可能需要先调用 set_time_limit(0) 避免超时
	 * 注意：数组的键值任意，但文件名前必须加@，使用单引号以避免本地路径斜杠被转义
	 */
	public function uploadMedia($data,$type,$media_identify,$is_temporary=1,$is_video=false,$video_info=[])
	{
		if($is_temporary) {
			$resoult = $this->weObj->uploadMedia($data, $type);
		}else{
            $resoult = $this->weObj->uploadForeverMedia($data, $type,$is_video,$video_info);
		}
		if(isset($resoult['errorcode'])||!$resoult){
			//@TODO 示例为无效媒体类型错误 {"errcode":40004,"errmsg":"invalid media type"}
			$this->weObj-> log($media_identify.'上传素材错误！'.date('Y-m-d H:i:s',time()));
			return false;
		}
		//入库
		dump($resoult);die;
		$wxMedia = new WXMedia();
		$wxMedia -> media_id = $resoult['media_id'];
		$wxMedia -> type = $type;
		$wxMedia -> media_identify = $media_identify;
		$wxMedia -> is_temporary = $is_temporary;
		try{
			$res = $wxMedia -> save();
		}catch (Exception $e){
		dump($resoult);die;
	}

		if(!$res){
			//记录日志
			return false;
		}
		return  $resoult['media_id'];
	}

	//发送模板消息
	public function  sendTemple()
	{
		//推送模板消息a
		$data ='
				{
					"touser":"onm1jwAD3vu1V53eaygfOmcfADfU",
					"template_id":"ryjfYS_SJ7o_6CNZwcDmFMZZGvi8125RZatIJb4OWQE",
					"url":"http://weixin.qq.com/download",
					"topcolor":"#FF0000",
					"data":{
					}
				}
		';
		$res = $this->weObj->sendTemplateMessage($data);
		dump($res);
	}

	//主动发送客服消息
	public function  customerSend()
	{
		//发送图片信息
		//PznOWd7iP3fYLR7NzHLm-4SxoaeHc53L4-SMyiIPHMKy4Y-Ot3byy9JYo9ea79xl刘诗诗mediaID
		//发送图片消息格式
		$data = '{
			"touser":"onm1jwAD3vu1V53eaygfOmcfADfU",
			"msgtype":"image",
			"image":
			{
			  "media_id":"PznOWd7iP3fYLR7NzHLm-4SxoaeHc53L4-SMyiIPHMKy4Y-Ot3byy9JYo9ea79xl"
			 }
		}';

		/*
		   $music_data = '{
						"touser":"onm1jwAD3vu1V53eaygfOmcfADfU",
						"msgtype":"music",
						"music":
						{
							"title":"音乐标题",
						  "description":"音乐描述",
						  "musicurl":"http://sc.111ttt.com/up/mp3/201037/5855D0C86B239D6D63430F8D25CEDE6C.mp3",
						  "hqmusicurl":"http://sc.111ttt.com/up/mp3/201037/5855D0C86B239D6D63430F8D25CEDE6C.mp3",
						  "thumb_media_id":"DvYbJV_a5Gv4CsTn4J4lvAyJ2CcOVF6idJOHCx3WJVvsVnSs6HHWc70WX3A2J3lq" 
						}
			}';
		发送图文消息 图文消息条数限制在10条以内，注意，如果图文数超过10，则将会无响应。
		$data = ['media' => '@'.config('upload_path').'wx_media/mengmeng.jpg'];
		$url = $this->weObj->uploadImg($data)['url'];
		$data1 = ['media' => '@'.config('upload_path').'wx_media/mengmeng1.jpg'];
        $url1 = $this->weObj->uploadImg($data1)['url'];
		//onm1jwHkwdW5h3ikwF3gsxLsdk14   onm1jwBg-wPluDGxzjyTdEI7usdE
			$tw_data = '
				{
				"touser":"onm1jwAD3vu1V53eaygfOmcfADfU",
				"msgtype":"news",
				"news":{
				"articles": [
					 {
						 "title":"happy day",
						 "description":"",
						 "url":"http://user.qzone.qq.com/514803678/4",
						 "picurl":"'.$url.'"
					 },
					 {
						 "title":" happy day",
						 "description":"",
						 "url":"http://user.qzone.qq.com/514803678/4",
						 "picurl":"'.$url1.'"
					 }
					 ]
					}
				}
			';
		*/
		//发送客服消息
		$res = $this ->weObj -> sendCustomMessage($data);
		dump($res);
	}
	public function getImgUrl($data)
	{
		//上传素材
		//$data = ['media' => '@'.config('upload_path').'wx_media/maomaochong1.jpg'];
		return  $this->weObj->uploadImg($data)['url'];


	}
	private function doEvent($RevEvent)
	{
		switch (strtolower($RevEvent['event'])) {
			case 'subscribe':
				// 相当于该用户注册，通常在user表中，增加该用户的记录， 最起码包含其openid
				// $sql = "insert into user (type, openid) values ('wx', '$msg->FromUserName')";
				// 获取用户更详细的信息
				$this->weObj->text("感谢您的关注！")->reply();

				break;
			case 'click':
				// 根据事件点击按钮的不同， 做不同的处理
				switch (strtolower($RevEvent['key'])) {
					case 'service_phone':// 签到事件
						$content = "服务电话：18734923636";
						$this->weObj->text($content)->reply();
						// 响应给微信服务器信息， 反馈到用户微信客户端上。

						break;

					case 'rselfmenu_2_0':
						$files[] = config('upload').'albums/11011114841131936682/0a0c7479521d020a36deb62ecef78e4f.jpg';
						$files[] = config('upload').'albums/11011114841131936682/0b83a980e3a2bf6441829b8595e32a19.jpeg';

						$index = mt_rand(0, 1);
						$this->weObj->sendImage($this->weObj-> getRevFrom(), $files[$index]);
						break;
					case 'image_text_key':
						$article_list[] = ['title'=>'article-1', 'description'=>'description-1', 'picUrl'=>'http://php.hellokang.net/images/pic04.jpg', 'url'=>'http://php.hellokang.net/newCase.html'];
						$article_list[] = ['title'=>'article-2', 'description'=>'description-2', 'picUrl'=>'http://php.hellokang.net/images/pic10.jpg', 'url'=>'http://php.hellokang.net/newCase.html'];

						$this->sendImageText($this->msg->ToUserName, $article_list);
						break;

				}

		}
	}
}
?>