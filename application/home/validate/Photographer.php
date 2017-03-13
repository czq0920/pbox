<?php 
namespace app\home\validate;
use app\common\common\Verify;
class Photographer extends Verify
{
	
	protected $rule = [
		'captcha|验证码'=>'require|captcha',
		'tokenGrapher' => 'require|token:tokenGrapher',
		'username' =>'require|verifyUser:6,20|unique:photographer',
		'realname' => 'require|verifyRealName:2,16',
		'mobile' => 'require|unique:photographer|verifyPhone',
		//'getCode' => "require|unique:session('restCode')",
		//'expire_time' => 'expire:2016-2-1,2016-10-01',
	];
	protected $message = [
	/**
	 * 'username.require' => '名称必须',
	'realname' => '名称最多不能超过25个字符',
	'age.number' => '年龄必须是数字',
	'age.between' => '年龄只能在1-120之间',
	'email' => '邮箱格式错误',
	 * */
		'getCode.unique' => "手机验证码错误！"
	];
}


 ?>