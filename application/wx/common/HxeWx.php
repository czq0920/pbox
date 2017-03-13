<?php
namespace app\wx\common;
use app\common\common\HxeSetting;
/**
 * 微信API类
 */
class HxeWx{
	
	private static $rawRequest;
	private static $requestArray;
	private static $requestType;
    private static $accessToken;
    private static $jsapiTicket;

    /**
     * 验证是否在微信开发模式后台环境中
     * @return bool
     */
    public static function isInWx(){
//        if( $_GET['signature'] == '8ELsEkAHpQyc8xG7TGD3' && $_GET['nonce'] == 'test' ) return true;
        $req = empty($GLOBALS['HTTP_RAW_POST_DATA']) ? file_get_contents('php://input') : $GLOBALS['HTTP_RAW_POST_DATA'];
        if( $_GET['signature'] && $_GET['timestamp'] && $_GET['nonce'] && $req ) return true;
        return false;
    }
	/**js - sdk
	 * wx.config({
	debug: true, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
	appId: '', // 必填，公众号的唯一标识
	timestamp: , // 必填，生成签名的时间戳
	nonceStr: '', // 必填，生成签名的随机串
	signature: '',// 必填，签名，见附录1
	jsApiList: [] // 必填，需要使用的JS接口列表，所有JS接口列表见附录2
	});
	 */
	/**
	 * 验证token
	 * @return bool
	 */
	private static function _verifyToken() {
		//测试后门
//		if ($_GET['signature'] == '8ELsEkAHpQyc8xG7TGD3' && $_GET['nonce'] == 'test') return true;
		$signature = addslashes(htmlspecialchars($_GET['signature']));
		$timestamp = addslashes(htmlspecialchars($_GET['timestamp']));
		$nonce = addslashes(htmlspecialchars($_GET['nonce']));
        //if(strlen($nonce)<10) return true;
		$tmpArr = array(config('WX_TOKEN'), $timestamp, $nonce);
		sort($tmpArr,SORT_STRING);
		$tmpStr = implode($tmpArr);
		$tmpStr = sha1($tmpStr);
		if ($tmpStr == $signature) return true;
        return false;
	}
	
	/**
	 * 获取用户发送的信息
	 */
	private static function _getRequest(){
		if (self::$requestArray) return;
		//验证Token
		if(!self::_verifyToken()) return;
		//网站接入验证
		if(isset($_GET['echostr'])) exit($_GET['echostr']);
		self::$rawRequest = empty($GLOBALS['HTTP_RAW_POST_DATA']) ? file_get_contents('php://input') : $GLOBALS['HTTP_RAW_POST_DATA'];
		if (!empty(self::$rawRequest)) {
			self::$requestArray = HxeUtil::obj2array(simplexml_load_string(self::$rawRequest, 'SimpleXMLElement', LIBXML_NOCDATA));
			self::$requestType = self::$requestArray['MsgType'];
		} 
	}
	
	/**
	 * 获取格式化为数组的用户发送的信息
	 * @return array
	 */
	public static function getRequest(){
		self::_getRequest();
		return self::$requestArray;
	}

    /**
     * 更改信息内容，用于hack
     * @param $key
     * @param $value
     */
	public static function setRequest($key,$value){
		self::_getRequest();
		if ($key == 'MsgType') self::$requestType = $value;
		self::$requestArray[$key] = $value;
	}
	
	/**
	 * 获取原始信息，XML格式
	 * @return string as xml
	 */
	public static function getRawRequest(){
		self::_getRequest();
		return self::$rawRequest;
	}

    /**
     * 自动回复
     * @param array $config
     * $config =
     * array(
     * array(
     * 'keyWords'        =>    array('1','wz','文字'),
     * 'replyType'        =>    'text',//text,news,music
     * 'replyContent'    =>    array('content'    =>    '回复文字信息'),
     * ),
     * array(
     * 'keyWords'        =>    array('2','yy','音乐'),
     * 'replyType'        =>    'music',//text,news,music
     * 'replyContent'    =>    array(
     * 'title'    =>    '音乐标题',
     * 'description'    =>    '音乐介绍',
     * 'musicUrl'    =>    'http://music.baidu.com',
     * 'HQMusicUrl'    =>    'http://music.baidu.com'),
     * ),
     * array(
     * 'keyWords'        =>    array('3','xw','新闻'),
     * 'replyType'        =>    'news',//text,news,music
     * 'replyContent'    =>    array(
     * 'newsArr'    =>    array(
     * array('title'    =>    '单条新闻')
     * ))
     * )
     * );
     * @return bool
     */
	public static function autoReply($config){
		$request = self::getRequest();
		if ($request['MsgType'] != 'text') return false;
		$keyWord = strtolower(trim($request['Content']));
		foreach ($config as $k=>$v){
			if (in_array($keyWord, $v['keyWords'])) {
				switch ($v['replyType']){
					case 'text' : 
						self::fetchTextResult($v['replyContent']['content']);
						break;
					case 'news':
						self::fetchNewsResult($v['replyContent']['newsArr']);
						break;
					case 'music':
						self::fetchMusicResult($v['replyContent']['title'], 
							$v['replyContent']['description'], 
							$v['replyContent']['musicUrl'],
							$v['replyContent']['HQMusicUrl']);
						break;
					case 'function' :
						call_user_func_array($v['replyContent']['function'], $v['replyContent']['para']);
						break;
					case 'method' :
						call_user_func_array(array($v['replyContent']['object'],$v['replyContent']['function']),  $v['replyContent']['para']);
						break;
					default: return false;
				}
			}
		}
		return false;
	}
	
	/**
	 * 获取用户发送信息的类型
	 * @return string "news","text","link","image","location","event"
	 */
	public static function getRequestType(){
		self::_getRequest();
		return self::$requestArray["MsgType"];
	}
	

	/**
	 * 获取用户微信ID（非用户注册ID）
	 * @return string
	 */
	public static function getRequestUserId(){
		self::_getRequest();
		return self::$requestArray["FromUserName"];
	}
	
	/**
	 * 返回纯文字消息
	 * @param string $content 文本内容
	 * @param bool $fetch 是否直接输出
	 * @param int $flag 是否星标（1是0否）
	 * @return string
	 */
	public static function fetchTextResult($content,$fetch=true,$flag=0){
		$xmlResult = '<Content><![CDATA[' . $content . ']]></Content>';
		return self::_getXmlResult('text', $xmlResult, $fetch, $flag);
	}

    /**
     * 将用户输入返回给多客服
     * @return string
     */
    public static function fetchCustomerService(){
        $request = self::getRequest();
        $xmlResult = '<xml>
<ToUserName><![CDATA['.$request["FromUserName"].']]></ToUserName>
<FromUserName><![CDATA['.$request["ToUserName"].']]></FromUserName>
<CreateTime>'.time().'</CreateTime>
<MsgType><![CDATA[transfer_customer_service]]></MsgType>
</xml>';
        exit($xmlResult);
    }
	
	/**
	 * 返回图文混排消息
	 * @param array $newsArr 图文混排消息内容，，至少一条，如：
	 * array(
	 * 	  array(
	 *      'title'			=> '标题',
	 *      'description'	=> '详细信息',//只有单条的混排消息才会显示
	 *      'picUrl'		=> '图片URL'，支持JPG、PNG格式，较好的效果为大图640*320，小图80*80
	 *      'url'			=> '链接地址'
	 *    )
	 *    ...
	 * )
	 * @param bool $fetch 是否直接输出
	 * @param int $flag 是否星标（1是0否）
	 * @return string
	 */
	public static function fetchNewsResult($newsArr,$fetch=true,$flag=0){
		$xmlResult = '<ArticleCount>' . count($newsArr) . '</ArticleCount>
<Articles>';
		foreach ($newsArr as $key=>$value) {
			$xmlResult .= '
<item>
<Title><![CDATA[' . ($value['title'] ? $value['title'] : '') . ']]></Title>
<Description><![CDATA[' . ($value['description'] ? $value['description'] : '') . ']]></Description>
<PicUrl><![CDATA[' . ($value['picUrl'] ? $value['picUrl'] : '') . ']]></PicUrl>
<Url><![CDATA[' . ($value['url'] ? $value['url'] : '') . ']]></Url>
</item>';
		}
		
		$xmlResult .= '
</Articles>';
		return self::_getXmlResult('news', $xmlResult, $fetch, $flag);
	}
	
	/**
	 * 返回音乐消息
	 * @param string $title 标题
	 * @param string $description 介绍
	 * @param string $musicUrl 音乐链接
	 * @param string $HQMusicUrl 高品质音乐链接（wifi默认播放这个）
	 * @param bool $fetch 是否直接输出
	 * @param int $flag 是否星标（1是0否）
	 * @return string
	 */
	public static function fetchMusicResult($title,$description,$musicUrl,$HQMusicUrl,$fetch=true,$flag=0){
		$xmlResult = '<Music>
<Title><![CDATA[' . $title . ']]></Title>
<Description><![CDATA[' . $description . ']]></Description>
<MusicUrl><![CDATA[' . $musicUrl . ']]></MusicUrl>
<HQMusicUrl><![CDATA[' . $HQMusicUrl . ']]></HQMusicUrl>
</Music>';
		return self::_getXmlResult('music', $xmlResult, $fetch, $flag);
	}

    /**
     * 输出xml结果
     * @param $msgType
     * @param $content
     * @param bool $fetch
     * @param int $flag
     * @return String - $xmlResult
     */
	private static function _getXmlResult($msgType,$content,$fetch=true,$flag=0) {
		$request = self::getRequest();
		$xmlResult = '<xml>
<ToUserName><![CDATA[' . $request["FromUserName"] . ']]></ToUserName>
<FromUserName><![CDATA[' . $request["ToUserName"] . ']]></FromUserName>
<CreateTime>' . time() . '</CreateTime>
<MsgType><![CDATA[' . $msgType . ']]></MsgType>
'.$content.'
<FuncFlag>'.$flag.'</FuncFlag>
</xml>';
		if ($fetch) exit($xmlResult);
		return $xmlResult;
	}

    /**
     * 发送测试的文本消息
     * @param string $content 文本内容
     * @param string $apiUrl API地址
     * @return string
     */
	public static function sendTestTextRequest($content,$apiUrl){
		return HxeUtil::doPost($apiUrl . '?signature=8ELsEkAHpQyc8xG7TGD3&nonce=test', '<xml>
 <ToUserName><![CDATA[toTestUser]]></ToUserName>
 <FromUserName><![CDATA[fromTestUser]]></FromUserName> 
 <CreateTime>'.time().'</CreateTime>
 <MsgType><![CDATA[text]]></MsgType>
 <Content><![CDATA['.$content.']]></Content>
 <MsgId>'.rand(0,10000000).'</MsgId>
 </xml>');
	}

    /**
     * 发送测试的其他类型消息
     * @param string $msgType
     * @param string $xml
     * 图片消息：
     * $msgType='image'
     * $xml='<PicUrl><![CDATA[this is a url]]></PicUrl>'
     * 地理位置消息
     * $msgType='location'
     * $xml='<Location_X>23.134521</Location_X>
     * <Location_Y>113.358803</Location_Y>
     * <Scale>20</Scale>
     * <Label><![CDATA[位置信息]]></Label>'
     * 链接消息
     * $msgType='link'
     * $xml='<Title><![CDATA[公众平台官网链接]]></Title>
     * <Description><![CDATA[公众平台官网链接]]></Description>
     * <Url><![CDATA[url]]></Url>'
     * 事件消息
     * $msgType='event'
     * $xml='<Event><![CDATA[事件类型，subscribe(订阅)、unsubscribe(取消订阅)、CLICK(自定义菜单点击事件)]]></Event>
     * <EventKey><![CDATA[事件KEY值，与自定义菜单接口中KEY值对应]]></EventKey>'
     * @param string $apiUrl API地址
     * @return string
     */
	public static function sendTestRequest($msgType,$xml,$apiUrl){
		return HxeUtil::doPost($apiUrl . '?signature=8ELsEkAHpQyc8xG7TGD3&nonce=test', '<xml>
 <ToUserName><![CDATA[toTestUser]]></ToUserName>
 <FromUserName><![CDATA[fromTestUser]]></FromUserName>
 <CreateTime>'.time().'</CreateTime>
 <MsgType><![CDATA['.$msgType.']]></MsgType>
'.$xml.'
 <MsgId>'.rand(0,10000000).'</MsgId>
 </xml>');
	}

    private static $baseUrl = 'https://api.weixin.qq.com/cgi-bin/';

    /**
     * @param bool $forceRefresh
     * @return bool
     */
    public static function getAccessToken($forceRefresh = false){
        if(self::$accessToken) return self::$accessToken;

        $savedToken = HxeSetting::get('WX_API_ACCESSTOKEN');
        if($savedToken['token'] && time() < ($savedToken['expire'] - 600) && !$forceRefresh){
            //D('Xu')->add(array('name'=>'token1','value'=> $savedToken['token']));
            return $savedToken['token'];
        }

        $appid = C('WX_APP_ID');
        $appSecret = C('WX_APP_SECRET');
        $result = json_decode(file_get_contents(self::$baseUrl . 'token?grant_type=client_credential&appid='.$appid.'&secret=' . $appSecret),true);
        if($result['errcode']){
            $_SESSION['SNS_API_ERROR'] = json_encode($result);
            return false;
        }
		HxeSetting::set('WX_API_ACCESSTOKEN','token',$result['access_token']);
        HxeSetting::set('WX_API_ACCESSTOKEN','expire',$result['expires_in'] + time());
		self::$accessToken = $result['access_token'];
        //D('Xu')->add(array('name'=>'token2','value'=> $accessToken;));
        return self::$accessToken;
    }

    public static function getJsapiTicket($forceRefresh = false){
        if(self::$jsapiTicket) return self::$jsapiTicket;
		$savedJsticket = HxeSetting::get('WX_API_JSAPI');
        if($savedJsticket['ticket'] && time() < ($savedJsticket['expire'] - 600) && !$forceRefresh){
            return $savedJsticket['ticket'];
        }
		$result = json_decode(file_get_contents(self::$baseUrl . "ticket/getticket?access_token=".self::getAccessToken()."&type=jsapi"),true);
        if($result['errcode']){
            $_SESSION['SNS_API_ERROR'] = json_encode($result);
            return false;
        }

        HxeSetting::set('WX_API_JSAPI','ticket',$result['ticket']);
        HxeSetting::set('WX_API_JSAPI','expire',$result['expires_in'] + time());

        self::$jsapiTicket = $result['ticket'];
        return self::$jsapiTicket;
    }

    /**
     * 发送文字服务信息
     * @param $wxId
     * @param $content
     * @return string
     */
    public static function pushTextMessage($wxId,$content){
        return self::_pushMessage(array(
            "touser"    =>  $wxId,
            "msgtype"   => "text",
            "text"      =>  array(
                'content'   =>  $content
            )
        ));
    }

    /**
     * 发送图片消息
     * @param $wxId
     * @param $mediaId
     * @return string
     */
    public static function pushImageMessage($wxId,$mediaId){
        return self::_pushMessage(array(
            "touser"    =>  $wxId,
            "msgtype"   => "image",
            "image"      =>  array(
                'media_id'   =>  $mediaId
            )
        ),1);
    }

    /**
     * 发送语音消息
     * @param $wxId
     * @param $mediaId
     * @return string
     */
    public static function pushVoiceMessage($wxId,$mediaId){
        return self::_pushMessage(array(
            "touser"    =>  $wxId,
            "msgtype"   => "voice",
            "voice"      =>  array(
                'media_id'   =>  $mediaId
            )
        ));
    }

    /**
     * 发送视频消息
     * @param $wxId
     * @param $mediaId
     * @param $thumbMediaId
     * @return string
     */
    public static function pushVideoMessage($wxId,$mediaId,$thumbMediaId){
        return self::_pushMessage(array(
            "touser"    =>  $wxId,
            "msgtype"   => "video",
            "video"      =>  array(
                'media_id'          =>  $mediaId,
                'thumb_media_id'    =>  $thumbMediaId
            )
        ));
    }

    /**
     * 发送音乐消息
     * @param $wxId
     * @param $title
     * @param $description
     * @param $musicurl
     * @param $hqmusicurl
     * @param $thumb_media_id
     * @return string
     */
    public static function pushMusicMessage($wxId,$title,$description,$musicurl,$hqmusicurl,$thumb_media_id){
        return self::_pushMessage(array(
            "touser"    =>  $wxId,
            "msgtype"   => "music",
            "music"      =>  array(
                'title'          =>  $title,
                'description'    =>  $description,
                'musicurl'       =>  $musicurl,
                'hqmusicurl'     =>  $hqmusicurl,
                'thumb_media_id' =>  $thumb_media_id
            )
        ));
    }

    /**
     * 发送新闻消息
     * @param $wxId
     * @param $newsArr
     * array(
     *      array(
     *      'title'            => '标题',
     *      'description'    => '详细信息',//只有单条的混排消息才会显示
     *      'picurl'        => '图片URL'，支持JPG、PNG格式，较好的效果为大图640*320，小图80*80
     *      'url'            => '链接地址'
     *    )
     *    ...
     * )
     * @return string
     */
    public static function pushNewsMessage($wxId,$newsArr){
        return self::_pushMessage(array(
            "touser"    =>  $wxId,
            "msgtype"   => "news",
            "news"      =>  array(
                'articles'          =>  $newsArr
        )));
    }

    private static function _pushMessage($rawContent,$type = 0){
        if($type){
            return HxeUtil::doPost( self::$baseUrl . 'message/custom/send?access_token=' . self::getAccessToken() , json_encode($rawContent));
        }else{
            return HxeUtil::doPost( self::$baseUrl . 'message/custom/send?access_token=' . self::getAccessToken() , json_encode($rawContent,JSON_UNESCAPED_UNICODE));
        }
        

    }

    /**
     * 获取用户信息
     * @param $wxId
     * @return mixed
     */
    public static function getUserInfo($wxId){
        if(!self::getAccessToken()) return array();
        return json_decode(file_get_contents(self::$baseUrl . 'user/info?access_token=' . self::getAccessToken() . '&openid=' . $wxId),true);
    }

    /**
     * 获取关注者列表
     * @param string $nextWxId
     * @return mixed
     */
    public static function getFollower($nextWxId = ''){
        $nextQuery = $nextWxId ?  '&next_openid=' . $nextWxId : '';
        return json_decode(file_get_contents(self::$baseUrl . 'user/get?access_token=' . self::getAccessToken() .$nextQuery),true);
    }

    public static function getTempQRcode($sceneId, $expire = 1800){
        if($expire > 1800) $expire = 1800;
        return self::_getQRCode('{"expire_seconds": '. $expire .', "action_name": "QR_SCENE", "action_info": {"scene": {"scene_id": '.$sceneId.'}}}');
    }

    public static function getPermernentQRCode($sceneId='',$sceneStr=''){
        if($sceneId > 100000) $sceneId = 100000;
        if($sceneId){
            return self::_getQRCode('{"action_name": "QR_LIMIT_SCENE", "action_info": {"scene": {"scene_id": '.$sceneId.'}}}');
        }elseif($sceneStr){
            return self::_getQRCode('{"action_name": "QR_LIMIT_STR_SCENE", "action_info": {"scene": {"scene_str": '.$sceneStr.'}}}');
        }
    }

    private static function _getQRCode($query){
        $result = json_decode(HxeUtil::doPost( self::$baseUrl . 'qrcode/create?access_token=' . self::getAccessToken() , $query),true);
        if($result['errcode']){
            $_SESSION['SNS_API_ERROR'] = json_encode($result);
            return false;
        }

        return 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=' . $result['ticket'];

    }

    public static function downloadMedia($mediaId,$urlOnly = false){
        $url = 'http://file.api.weixin.qq.com/cgi-bin/media/get?access_token='.self::getAccessToken().'&media_id=' . $mediaId;
        if($urlOnly) return $url;
        return file_get_contents($url);
    }

	public static function sendTemplateMessage($data) {
        return HxeUtil::doPost( self::$baseUrl . 'message/template/send?access_token=' . self::getAccessToken() , self::json_encode($data));
	}
                                                                                                                                                  
	/**
	 * 微信api不支持中文转义的json结构
	 * @param array $arr
	 */
	static function json_encode($arr) {
		$parts = array ();
		$is_list = false;
		//Find out if the given array is a numerical array
		$keys = array_keys ( $arr );
		$max_length = count ( $arr ) - 1;
		if (($keys [0] === 0) && ($keys [$max_length] === $max_length )) { //See if the first key is 0 and last key is length - 1
			$is_list = true;
			for($i = 0; $i < count ( $keys ); $i ++) { //See if each key correspondes to its position
				if ($i != $keys [$i]) { //A key fails at position check.
					$is_list = false; //It is an associative array.
					break;
				}
			}
		}
		foreach ( $arr as $key => $value ) {
			if (is_array ( $value )) { //Custom handling for arrays
				if ($is_list)
					$parts [] = self::json_encode ( $value ); /* :RECURSION: */
				else
					$parts [] = '"' . $key . '":' . self::json_encode ( $value ); /* :RECURSION: */
			} else {
				$str = '';
				if (! $is_list)
					$str = '"' . $key . '":';
				//Custom handling for multiple data types
				if (is_numeric ( $value ) && $value<2000000000)
					$str .= '"' . $value . '"'; //Numbers
				elseif ($value === false)
				$str .= 'false'; //The booleans
				elseif ($value === true)
				$str .= 'true';
				else
					$str .= '"' . addslashes ( $value ) . '"'; //All other things
				// :TODO: Is there any more datatype we should be in the lookout for? (Object?)
				$parts [] = $str;
			}
		}
		$json = implode ( ',', $parts );
		if ($is_list)
			return '[' . $json . ']'; //Return numerical JSON
		return '{' . $json . '}'; //Return associative JSON
	}

    /*
     * 将用户转移到总监用户组下 xu
    */
    public static function groupSet($openid,$group_id=0){
        if($openid){

            $token = self::getAccessToken();
            $getMemUrl = 'https://api.weixin.qq.com/cgi-bin/groups/members/update?access_token='.$token;
            $data = '{"openid":"'.$openid.'","to_groupid":'.$group_id.'}';
            $c = HxeUtil::doPost($getMemUrl, $data);
            return $c;
        }
        return false;
    }
}

?>
