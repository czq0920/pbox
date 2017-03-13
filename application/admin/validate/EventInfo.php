<?php 
namespace app\admin\validate;
use app\common\common\Verify;
class EventInfo extends Verify
{
	protected $rule = [
		//'captcha|验证码'=>'require|captcha',
		'tokenAdminEvent' => 'require|token:tokenAdminEvent',
		'company_name' => 'verifyRealName:2,26',
		'company_contact' => 'verifyRealName:2,26',
		//'company_contact_mobile' => 'verifyPhone',
		'event_address'  => 'verifyRealName:2,100',
		'event_type'  => 'verifyRealName:2,26',
		'event_number'  => 'number',
		'event_consult'  => 'verifyRealName:2,26',
		// 'event_consult_mobile'  => 'verifyPhone',
		'event_start_time'  => 'date',
		'event_stop_time'  => 'date',
	];
	protected $message = [
		//'company_contact_mobile.verifyPhone' => '顾客手机号格式不正确',
		//'event_consult_mobile.verifyPhone' => '顾问手机号格式不正确',
		'event_start_time.dateFormat' => '会议开始时间格式不正确',
		'event_stop_time.dateFormat' => '会议开始时间格式不正确',
	];
}


 ?>