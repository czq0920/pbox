<?php
namespace app\common\common;
/**
 * 
 * 工具类
 *
 */
class HxeUtil {
	private static $timeCounting;
	
	/**
	 * 开始计时，输出开始标记
	 */
	public static function startTimeCounting() {
		self::$timeCounting = self::getMicrotime ();
		echo '<br />-------------Start--------------<br />';
	}
	
	/**
	 * 结束计时，输出结束标记
	 */
	public static function stopTimeCounting() {
		$time = self::getMicrotime () - self::$timeCounting;
		echo '<br />-------------Stop------------------<br />';
		echo 'Elapse Time: ' . $time . 's';
		echo '<br />====================================<br />';
	}
	
	/**
	 * 获取毫秒时间
	 *
	 * @return number
	 */
	public static function getMicrotime() {
		$mtime = microtime ();
		list ( $m, $s ) = explode ( ' ', $mtime );
		return $m + $s;
	}
	
	/**
	 * 将object递归转换为数组
	 *
	 * @param object $obj        	
	 * @return array
	 */
	public static function obj2array($obj) {
		$_array = is_object ( $obj ) ? get_object_vars ( $obj ) : $obj;
		foreach ( $_array as $key => $value ) {
			$value = (is_array ( $value ) || is_object ( $value )) ? self::obj2array ( $value ) : $value;
			$array [$key] = $value;
		}
		return $array;
	}
	public static function json2array($json) {
		return self::obj2array ( json_decode ( $json, true ) );
	}
	public static function utfSubstr($str, $len, $more = false) {
		if (function_exists ( 'mb_substr' )) {
			$ret = mb_substr ( $str, 0, $len, 'UTF-8' );
			if (mb_strlen ( $str ) > mb_strlen ( $ret ) && $more)
				$ret .= '...';
			return $ret;
		}
		
		$org = $str;
		for($i = 0; $i < $len; $i ++) {
			$temp_str = substr ( $str, 0, 1 );
			if (ord ( $temp_str ) > 127) {
				$i ++;
				if ($i < $len) {
					$new_str [] = substr ( $str, 0, 3 );
					$str = substr ( $str, 3 );
				}
			} else {
				$new_str [] = substr ( $str, 0, 1 );
				$str = substr ( $str, 1 );
			}
		}
		$ret = join ( $new_str );
		if (strlen ( $org ) > strlen ( $ret ) && $more)
			$ret .= '...';
		return $ret;
	}
	
	/**
	 * 检测IP攻击
	 */
//	public static function checkHackIp() {
//		// 检测部分
//		$ip = get_client_ip ();
//
//		// 微信IP段白名单：101.226.0.0/16
//		if (substr ( $ip, 0, 7 ) == '101.226')
//			return;
//
//			// 配置文件中的封禁IP列表
//		$ipList = C ( 'HACK_IP_LIST' );
//		foreach ( $ipList as $k => $v ) {
//			if (strpos ( $ip, $v ) !== false)
//				ImDelegate::fetchError ( ImDelegate::$_ERROR_REQUEST_HACK_IP );
//		}
//		// 从memcache中取得自动封禁的IP列表
//		$ipListAuto = ImMemcache::get ( 'HACK_IP_LIST' );
//		$ipListAuto = $ipListAuto != false ? json_decode ( $ipListAuto, true ) : array ();
//		$ipListAuto = is_array ( $ipListAuto ) ? $ipListAuto : array ();
//		foreach ( $ipListAuto as $k => $v ) {
//			$time = time ();
//			// 将封禁到期的IP移出封禁列表
//			if ($v ['t'] < time ()) {
//				unset ( $ipListAuto [$k] );
//                ImMemcache::set ( 'HACK_IP_LIST', json_encode ( $ipListAuto ), 3600 * 24 * 7 ); // 全列表保存7天
//			}
//			if (strpos ( $ip, $v ['ip'] ) !== false)
//                ImDelegate::fetchError ( ImDelegate::$_ERROR_REQUEST_HACK_IP );
//		}
//
//		// 自动判断频繁请求
//		// 统计一个时间段内同一ip请求数量
//		$count = ImMemcache::increment ( 'IP_RECORD_' . $ip );
//		// 时间段过期，重新统计
//		if (! $count)
//			ImMemcache::set ( 'IP_RECORD_' . $ip, 1, C ( 'HACK_IP_TEST_PERIOD' ) );
//			// 超限，则加入自动屏蔽列表
//		elseif ($count > C ( 'MAX_REQUEST_PER_PERIOD' )) {
//			$ipListAuto [$ip] = array (
//					'ip' => $ip,
//					't' => time () + C ( 'HACK_IP_BLOCK_TIME' )
//			);
//			ImMemcache::set ( 'HACK_IP_LIST', json_encode ( $ipListAuto ), 3600 * 24 * 7 ); // 全列表保存7天
//		}
//		return;
//	}
	
	/**
	 * 清空IP攻击列表
	 */
//	public static function clearHackIpList() {
//		// 从memcache中取得自动封禁的IP列表
//		$ipListAuto = ImMemcache::get ( 'HACK_IP_LIST' );
//		$ipListAuto = $ipListAuto != false ? json_decode ( $ipListAuto, true ) : array ();
//		$ipListAuto = is_array ( $ipListAuto ) ? $ipListAuto : array ();
//		foreach ( $ipListAuto as $k => $v ) {
//			ImMemcache::clear ( 'IP_RECORD_' . $v ['ip'] );
//		}
//
//		ImMemcache::clear ( 'HACK_IP_LIST' );
//		return;
//	}

    /**
     * 获取流逝时间字符串
     *
     * @param int $timestamp
     *            要计算的时间的时间戳
     * @param int|string $timestampNow
     *            参考时间，不传则为现在，时间戳
     * @return string
     */
	public static function getPastTimeString($timestamp, $timestampNow = '') {
		$timestampNow = $timestampNow ? $timestampNow : time ();
		$lastTime = $timestampNow - $timestamp;
		if ($lastTime < 60)
			return $lastTime . '秒前';
		$lastTime = floor ( $lastTime / 60 );
		if ($lastTime < 60)
			return $lastTime . '分钟前';
		$lastTime = floor ( $lastTime / 60 );
		if ($lastTime < 24)
			return $lastTime . '小时前';
		$lastTime = floor ( $lastTime / 24 );
		if ($lastTime < 30)
			return $lastTime . '天前';
		$lastTime = floor ( $lastTime / 30 );
		if ($lastTime < 12)
			return $lastTime . '个月前';
		$lastTime = floor ( $lastTime / 12 );
		return $lastTime . '年前';
	}

    /**
     * 快速完成一个Curl请求
     *
     * @param string $url
     * @param array $fields
     *            POST字段，传入数组
     * @param string $method
     *            = 'post'
     * @param bool $debug
     * @return string 结果
     */
	public static function curl($url, $fields = array(), $method = 'post',$debug = false) {
		$curl = curl_init ();
		curl_setopt ( $curl, CURLOPT_URL, $url );
		curl_setopt ( $curl, CURLOPT_HTTPHEADER, array (
				'Expect:' 
		) );
		curl_setopt ( $curl, CURLOPT_TIMEOUT, 60 );
		curl_setopt ( $curl, CURLOPT_MAXREDIRS, 6 );
		curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, true );
		curl_setopt ( $curl, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt ( $curl, CURLOPT_COOKIEJAR, "/tmp/curl_cookie_file" );
		curl_setopt ( $curl, CURLOPT_COOKIEFILE, "/tmp/curl_cookie_file" );
		if (strtolower ( $method ) == 'post') {
			curl_setopt ( $curl, CURLOPT_POST, true );
			curl_setopt ( $curl, CURLOPT_POSTFIELDS, http_build_query ( $fields ) );
		}
		curl_setopt ( $curl, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)' );

        $result = curl_exec ( $curl );
        if($debug){
            echo "=====post data======\r\n";
            var_dump($fields);
            echo '=====info====='."\r\n";
            print_r( curl_getinfo($curl) );
            echo '=====$response====='."\r\n";
            print_r( $result );
            echo '=====error====='."\r\n";
            echo  curl_error($curl) ;
        }
		return $result;
	}
	
	/**
	 * 执行POST操作
	 *
	 * @param string $url        	
	 * @param mix $data        	
	 * @param mix $cookie        	
	 * @return string
	 */
	public static function doPost($url, $data, $cookie = null) {
		if (is_array ( $data )) {
			ksort ( $data );
			$data = http_build_query ( $data );
		}
		if (is_array ( $cookie )) {
			$cookie = http_build_query ( $cookie );
		}
		$opts = array (
				'http' => array (
						'method' => "POST",
						'timeout'=>5,
						'header' => "Content-type: text/html\r\n" . 
						// "Content-length:" . strlen($data) . "\r\n" .
						"Cookie: {$cookie}\r\n" . "\r\n",
						'content' => $data 
				) 
		);
		// print_r($opts);
		$cxContext = stream_context_create ( $opts );
		$sFile = file_get_contents ( $url, false, $cxContext );
		return $sFile;
	}

	/*
	 * 执行一个get请求
	*/
	public static function doGet($url){
		if($url){
			$ch = curl_init($url) ;
	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ; // 获取数据返回  
	        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true) ; // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回  
	        $output = curl_exec($ch) ;
	        curl_close($ch);
	        return $output;
		}
		return false;
	}//方法的结束符
	
	/**
	 * 生成随机字符串
	 * 
	 * @param int $len
	 *        	生成长度
	 * @param string $type
	 *        	生成类型，包括num(0-9)、char(a-z)、charnum(0-9+a-z)、all(0-9+a-z+符号)。默认为num
	 * @param string $model
	 *        	自定义生成字符的范围
	 * @return string
	 */
	public static function generateString($len, $type = 'num', $model = '') {
		$models = array (
				'num' => '0123456789',
				'char' => 'abcdefghijklmnopqrstuvwxyz',
				'all' => 'abcdefghijklmnopqrstuvwxyz0123456789_=%&*<>|',
				'char&num' => 'abcdefghijklmnopqrstuvwxyz0123456789' 
		);
		if (! $model)
			$model = $models [$type];
		if (! $model || ! len)
			return false;
		
		$ret = '';
		for($i = 0; $i < $len; $i ++) {
			$n = mt_rand ( 0, strlen ( $model ) - 1 );
			$ret .= $model {$n};
		}
		return $ret;
	}
	
	/**
	 * 模拟js的unescape函数
	 * 
	 * @param unknown_type $str        	
	 * @return Ambigous <string, unknown>
	 */
	public static function unescape($str) {
		$ret = '';
		$len = strlen ( $str );
		
		for($i = 0; $i < $len; $i ++) {
			if ($str [$i] == '%' && $str [$i + 1] == 'u') {
				$val = hexdec ( substr ( $str, $i + 2, 4 ) );
				
				if ($val < 0x7f)
					$ret .= chr ( $val );
				else if ($val < 0x800)
					$ret .= chr ( 0xc0 | ($val >> 6) ) . chr ( 0x80 | ($val & 0x3f) );
				else
					$ret .= chr ( 0xe0 | ($val >> 12) ) . chr ( 0x80 | (($val >> 6) & 0x3f) ) . chr ( 0x80 | ($val & 0x3f) );
				
				$i += 5;
			} else if ($str [$i] == '%') {
				$ret .= urldecode ( substr ( $str, $i, 3 ) );
				$i += 2;
			} else
				$ret .= $str [$i];
		}
		return $ret;
	}
	
	/**
	 * 弹出HTTP验证框
	 * 
	 * @return array( 用户名, 密码 )
	 */
	public static function httpAuthLogin() {
		$key = 'HTTP_AUTH_LOGIN_SID';
		$val = HxeCookie::get ( $key );
		if (! $_SERVER ['PHP_AUTH_USER'] || ! $_SERVER ['PHP_AUTH_PW'] || ($val == 1 && ! HxeCookie::get ( 'HTTP_AUTH_LOGIN_SUCCESS' ))) {
			HxeCookie::set ( $key, 0, 3600 );
			self::_httpAuth ();
		} else {
			HxeCookie::set ( $key, 1, 3600 );
			return array (
					$_SERVER ['PHP_AUTH_USER'],
					$_SERVER ['PHP_AUTH_PW'] 
			);
		}
	}
	private static function _httpAuth() {
		header ( 'WWW-Authenticate: Basic realm="' . $_SERVER ['HTTP_HOST'] . '"' );
		header ( 'HTTP/1.0 401 Unauthorized' );
		print 'Access deny!';
		exit ();
	}

    /**
     * 生成验证码
     * @param bool|string $useImgBg 是否使用背景图片
     * @param bool|string $useNoise 是否添加杂点
     * @param bool|string $useCurve 是否绘制干扰线
     * @param bool|string $useZh 是否使用中文验证码
     * @param int|number $length 验证码字符数
     */
//	public static function getVerificationCode($useImgBg = true, $useNoise = true, $useCurve = false, $useZh = false, $length = 4) {
//        Vendor("Captcha.Captcha");
//		//require dirname ( dirname ( __FILE__ ) ) . '/Captcha/Captcha.php';
//		\Captcha::$useImgBg = $useImgBg;
//		\Captcha::$useNoise = $useNoise;
//		\Captcha::$useCurve = $useCurve;
//		\Captcha::$useZh = $useZh;
//		\Captcha::$fontSize = 20;
//		\Captcha::$length = $length;
//		\Captcha::entry ();
//	}
	
	/**
	 * 验证code是否正确
	 * @param string $secode
	 * @return boolean
	 */
//	public static function checkVerificationCode($secode){
//		if(empty($secode))return false;
//		//require dirname ( dirname ( __FILE__ ) ) . '/Captcha/Captcha.php';
//        Vendor("Captcha.Captcha");
//		$ret = \Captcha::check($secode);
//		session_destroy();
//		if($ret){
//			return true;
//		}else{
//			return false;
//		}
//	}

//    public static function ldapVerify($mailName,$password){
//        $ldapConn = ldap_connect( '60.29.244.218', 389 );
//        ldap_set_option( $ldapConn, LDAP_OPT_PROTOCOL_VERSION, 3 );
//        ldap_set_option( $ldapConn, LDAP_OPT_REFERRALS, 0 );
//        $ldapBind = ldap_bind( $ldapConn, "IM20\\".$mailName, $password );
//        ldap_close($ldapConn);
//
//        return $ldapBind;
//
//    }

    /**
     * 对提供的数据进行urlsafe的base64编码。
     *
     * @param string $data 待编码的数据，一般为字符串
     *
     * @return string 编码后的字符串
     * @link http://developer.qiniu.com/docs/v6/api/overview/appendix.html#urlsafe-base64
     */
    public static function base64_urlSafeEncode($data)
    {
        $find = array('+', '/');
        $replace = array('-', '_');
        return str_replace($find, $replace, base64_encode($data));
    }

    /**
     * 对提供的urlsafe的base64编码的数据进行解码
     *
     * @param string $data 待解码的数据，一般为字符串
     *
     * @return string 解码后的字符串
     */
    public static function base64_urlSafeDecode($str)
    {
        $find = array('-', '_');
        $replace = array('+', '/');
        return base64_decode(str_replace($find, $replace, $str));
    }


    /**
    * @author: vfhky 20130304 20:10
    * @description: PHP调用新浪短网址API接口
    *    * @param string $type: 非零整数代表长网址转短网址,0表示短网址转长网址
    */
    protected function xlUrlAPI($type,$url){

        $key = '532734211';
        if($type)
        $baseurl = 'http://api.t.sina.com.cn/short_url/shorten.json?source='.$key.'&url_long='.$url;
        else
        $baseurl = 'http://api.t.sina.com.cn/short_url/expand.json?source='.$key.'&url_short='.$url;
        $ch=curl_init();
        curl_setopt($ch, CURLOPT_URL,$baseurl);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        $strRes=curl_exec($ch);
        curl_close($ch);
        $arrResponse=json_decode($strRes,true);
        if (isset($arrResponse->error) || !isset($arrResponse[0]['url_long']) || $arrResponse[0]['url_long'] == '')
        return 0;
        if($type)
        return $arrResponse[0]['url_short'];
        else
        return $arrResponse[0]['url_long'];
    }


    /** 
     * 过滤输入项中的特殊字符
    */
    public function customFilter($name){

        $pattern[0] = '/\&/';
        $pattern[1] = '/</';
        $pattern[2] = "/>/";
        $pattern[3] = '/\n/';
        $pattern[4] = '/\(/';
        $pattern[5] = '/\)/';

        $replacement[0] = ' ';
        $replacement[1] = ' ';
        $replacement[2] = ' ';
        $replacement[3] = ' ';
        $replacement[4] = ' ';
        $replacement[5] = ' ';
        return preg_replace($pattern, $replacement, $name);
    }//方法的结束符


    /**
     * 手机号的正则判断
    */
    public function customMobileVerify($mobile){
    	$res = preg_match("/^1[34578]\d{9}$/", $mobile);
    	return $res;
    }//方法的结束符


    /** 
     * 计算当前时间到第二天5点的是时间差
     */
    public function getLoginTime(){
    	$set_time = '05:00:00';
    	$time = time();
        $login_time_str = date('Y-m-d',$time) . $set_time;
        $login_time = strtotime($login_time_str);

        if($time < $login_time){
            $date_end = $login_time_str;
        }else{
            $date_end = date('Y-m-d',strtotime('+1 day')) . $set_time;
        }

        return strtotime($date_end) - time();
    }//方法的结束符

}

?>