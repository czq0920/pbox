<?php
namespace app\wx\common;
use app\common\common\AES;
class HxeWxDispatcher {

//    static function getUidByWxId($WxId=''){
//        if ($WxId)
//            return D('User')->where(array('openid'=>$WxId))->getField('uid');
//        return D('User')->where(array('openid'=>HxeWx::getRequestUserId()))->getField('uid');
//    }
//
//    static function hongbao($openid=''){
//
//        $uid = self::getUidByWxId($openid);
//
//        $uid_aes = HxeUtil::base64_urlSafeEncode(AES::encode(C('SALES_REQUIRE_AES_KEY'),$uid));
//
//        $news = array(
//            array(
//                'title'       => '【点击进入】参加现场人气王活动！赢取现金奖励！',
//                'description' => '欢迎参加【会小二广州发布会】，赶快了解并参加现场人气王活动吧！',
//                'picUrl'      => 'http://img.yaokaihui.com/annual/welcome.jpg',
//                'url'         => C('CAMPAIGN_URL') . $uid_aes,
//            )
//        );
//
//        HxeWx::fetchNewsResult($news);
//
//    }


}

?>