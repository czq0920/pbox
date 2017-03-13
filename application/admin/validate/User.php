<?php 
namespace app\admin\validate;
use think\Validate;
class User extends Validate
{
	protected $rule = [
		'tokenUser' => 'require|token:tokenUser',
		'username' => 'require|max:25',
		//'realname' => 'require|min:2',
		//'mobile' => 'require|length:11|number',
		//'password'=>'password'
		//'captcha|验证码'=>'require|captcha',
	];
	protected $message = [

	'username.require' => '名称必须',
	//'realname' => '名称最少两个不能超过25个字符',
	'age.number' => '年龄必须是数字',
	'age.between' => '年龄只能在1-120之间',
	'email' => '邮箱格式错误',
	];
}


 ?>