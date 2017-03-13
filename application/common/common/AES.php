<?php
namespace app\common\common;
/**
 * 利用mcrypt做AES加密解密
 */

abstract class AES{

    //算法,另外还有192和256两种长度
    const CIPHER = MCRYPT_RIJNDAEL_128;

    //模式
    const MODE = MCRYPT_MODE_ECB;

    /**
     * 加密
     * @param string $key 密钥
     * @param string $str 需加密的字符串
     * @return string
     */
    static public function encode( $key, $str ){
        $iv = mcrypt_create_iv(mcrypt_get_iv_size(self::CIPHER,self::MODE),MCRYPT_RAND);
        return mcrypt_encrypt(self::CIPHER, $key, $str, self::MODE, $iv);
    }

    /**
     * 解密
     * @param string $key 密钥
     * @param string $str 需解密的字符串
     * @return string
     */
    static public function decode( $key, $str ){
        $iv = mcrypt_create_iv(mcrypt_get_iv_size(self::CIPHER,self::MODE),MCRYPT_RAND);
        return mcrypt_decrypt(self::CIPHER, $key, $str, self::MODE, $iv);
    }

}

//echo '<xmp>';
//$cipher_list = mcrypt_list_algorithms();//mcrypt支持的加密算法列表
//$mode_list = mcrypt_list_modes();   //mcrypt支持的加密模式列表
//
//$str = '测试字符串';
//$key = 'aSGJLGYEWERWRREW4567i8o';
//$str1=AES::encode($key, $str);
//$str2=AES::decode($key, $str1);

//urlencode(str_replace('/','*',base64_encode(AES::encode(C('AES_KEY'),$require_id))));//加密
//intval(AES::decode(C('AES_KEY'),base64_decode(str_replace('*','/',$share_id_aes))));//解密

//HxeUtil::base64_urlSafeEncode(AES::encode(C('SALES_REQUIRE_AES_KEY'),$uid));加密
//AES::decode(C('SALES_REQUIRE_AES_KEY'),HxeUtil::base64_urlSafeDecode($uid))解密

?>