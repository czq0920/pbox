<?php 
namespace app\wx\validate;
use think\Validate;
class WXMedia extends Validate
{
	protected $rule = [
		'media_id' => 'require|unique',

	];
	protected $message = [
		'media_id.unique' => 'media_id已经存在！'
	];
}


 ?>