<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------    
return [
    'mail'=>[
        'Host'=>'smtp.163.com',
        'From'=>'18734923636@163.com',
        'FromName' => 'czq',
        'Username' => '18734923636',
        'Password' => 'czq0920'
    ],
    'rest'=>[
        //主帐号,对应开官网发者主账号下的 ACCOUNT SID
        'accountSid' => '8aaf070858230abd01582481c24e0210',

        //主帐号令牌,对应官网开发者主账号下的 AUTH TOKEN
        'accountToken' => 'd6031505bb4e49fd8f903a740d703046',

        //应用Id，在官网应用列表中点击应用，对应应用详情中的APP ID
        //在开发调试的时候，可以使用官网自动为您分配的测试Demo的APP ID
        'appId' => '8aaf070858230abd01582481c31e0215',

        //请求地址
        //沙盒环境（用于应用开发调试）：sandboxapp.cloopen.com
        //生产环境（用户应用上线使用）：app.cloopen.com
        'serverIP' => 'app.cloopen.com',


        //请求端口，生产环境和沙盒环境一致
        'serverPort' => '8883',

        //REST版本号，在官网文档REST介绍中获得。
        'softVersion' => '2013-12-26',
    ],
    //头像thumb验证配置
    /**
     * size 上传文件的最大字节1M=1024k=1048576字节
    ext 文件后缀，多个用逗号分割或者数组
    type 文件MIME类型，多个用逗号分割或者数组
     * 还有一个额外的自动验证规则是，如果上传的文件后缀是图像文件后缀，则会检查该文件是否是一个合法
    的图像文件。
     */
   

];
