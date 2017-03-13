<?php 
namespace app\home\validate;
use app\common\common\Verify;
class Member extends Verify
{
	protected $rule = [
		//'captcha|验证码'=>'require|captcha',
		'tokenMember' => 'require|token:tokenMember',
		//'username' =>'require|verifyUser:6,20|unique:photographer',
		'realname' => 'require|verifyUser:2,10',
		'mobile' => 'require|unique:photographer|verifyPhone',
		'getCode' => "require|unique:session('restCode')",
	];
	protected $message = [
		'mobile.unique' => '手机号不能重复',
	];
}


 ?>