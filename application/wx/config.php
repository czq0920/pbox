<?php

return [
    'options' => [
        'token'=>'czqczq', //填写你设定的key
        'encodingaeskey'=>'hUKt6c8dEq5pR1s5XRTHVVrwXzRl9C8j6wUk1qLrs5W', //填写加密用的EncodingAESKey
        //'appid'=>'wx7d4523b9a2b74293', //填写高级调用功能的app id
        'appid'=> IS_WXTESTING?'wx7d4523b9a2b74293':'wx66e0ecd97567e7c6',                 //wx66e0ecd97567e7c6
        'appsecret'=> IS_WXTESTING?'a82e0bb688feeab527e19020a5deb192':'d2812e92916637442858ddd9c806bd2e', //填写高级调用功能的密钥----d2812e92916637442858ddd9c806bd2e//订阅号
        'debug' => true ,//开启记录日志
        'logcallback' => false
    ],
    /* 微信设置 */
    //'WX_TOKEN'                  =>  'czqczq' , //@TODO 修改Token(3-32位字母数字组合)
    //'WX_APP_ID'                 =>  'wx7d4523b9a2b74293',               //@TODO 修改AppId
   // 'WX_APP_SECRET'            =>  'a82e0bb688feeab527e19020a5deb192',  //@TODO 修改AppSecret
    'wx_sys_number'   => 'gh_059b615dfb7a',//@TODO 修改公众账号
];