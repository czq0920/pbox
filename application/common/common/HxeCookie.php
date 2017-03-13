<?php
namespace app\common\common;
/**
 * 处理Cookie
 */
class HxeCookie {

    /**
     * 设置Cookie
     * @param string $key
     * @param array $value 将被序列化
     * @param int|string $expire
     */
    static function set($key,$value,$expire='',$prefix=''){
        $arr[$key] = $value;
        $arr['MDK'] = self::makeMDK($arr);

        $expire =   !empty($expire)?    time() + $expire   :  0;
        $arr   =  base64_encode(serialize($arr));

        $prefix = $prefix ? $prefix : C('COOKIE_PREFIX');
        setcookie($prefix.$key, $arr,$expire,C('COOKIE_PATH'),C('COOKIE_DOMAIN'),0,1);
        $_COOKIE[$prefix.$key]  =   $arr;
    }

    /**
     * 取得Cookie，若不合法将被清空，并返回false
     * @param string $key
     * @return array()
     */
    static function get($key,$prefix=''){
        $prefix = $prefix ? $prefix : C('COOKIE_PREFIX');
        $value   = $_COOKIE[$prefix.$key];
        $value   =  unserialize(base64_decode($value));
        if (!self::checkIllegal($value)){
            self::clear($key);
            return false;
        }
        unset($value['MDK']);
        return $value[$key];
    }

    /**
     * 清除Cookie
     * @param string $key
     */
    static function clear($key,$prefix=''){
        $prefix = $prefix ? $prefix : C('COOKIE_PREFIX');
        setcookie($prefix.$key, '',-3600,C('COOKIE_PATH'),C('COOKIE_DOMAIN'));
        unset($_COOKIE[C('COOKIE_PREFIX').$key]);
    }

    /**
     * 检查原始cookie数组是否合法
     * @param array $value
     * @return bool
     */
    private static function checkIllegal($value){
        $arr = $value;
        unset($arr['MDK']);
        if ($value['MDK'] != self::makeMDK($arr))
            return false;
        return true;
    }


    /**
     * 生成cookie的key
     * @param array $arr
     * @return bool|string
     */
    private static function makeMDK( $arr ){
        if( !is_array($arr) ) return false;
        if( count( $arr ) ){
            ksort($arr);
            $str = serialize($arr) . '_' . SECURE_CODE;
        }else{
            $str = SECURE_CODE;
        }
        return md5( $str );
    }


}
?>