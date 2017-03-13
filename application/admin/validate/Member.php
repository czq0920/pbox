<?php 
namespace app\admin\validate;
use app\common\common\Verify;
class Member extends Verify
{
	protected $rule = [
		//'captcha|验证码'=>'require|captcha',
		'tokenAdminMember' => 'require|token:tokenAdminMember',
		//'username' =>'require|verifyUser:6,20|unique:photographer',
		'realname' => 'require|verifyRealName:2,26',
		'mobile' => 'require|unique:member|verifyPhone',
		'order_date' => 'require|date'
	];
	protected $message = [
		'mobile.unique' => '手机号不能重复',
	];
}


 ?>