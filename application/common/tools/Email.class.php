<?php
namespace framework\tools;
use framework\tools\PHPMailer\PHPMailer;
class Email
{
    public function sendEmail($from,$to,$title,$content)
    {
        //加载PHPMailer类文件
        //实例化对象
        $mail = new PHPMailer();
        
        //设置属性（配置服务器账号、密码等）
        //3.设置属性，告诉我们的服务器，谁跟谁发送邮件
        $mail -> IsSMTP();			   //告诉服务器使用smtp协议发送
        $mail -> SMTPAuth = true;		//开启SMTP授权
        $mail -> Host = 'smtp.163.com';	//告诉我们的服务器使用163的smtp服务器发送
        $mail -> From = $from;	//发送者的邮件地址（使用这个邮箱给别人发送邮件）
        
        $mail -> FromName = 'itbull';		//发送邮件的用户昵称
        $mail -> Username = 'alsothank';	//登录到163的邮箱的用户名
        $mail -> Password = 'itbullphp04';	//第三方登录的授权码，在邮箱里面设置
        
        //编辑发送的邮件内容
        $mail -> IsHTML(true);		//发送的内容使用html编写
        $mail -> CharSet = 'utf-8';		//设置发送内容的编码
        $mail -> Subject = $title;	//设置邮件的主题、标题
        $mail -> MsgHTML($content);			//发送的邮件内容主体
        
        //告诉服务器接收人的邮件地址
        $mail -> AddAddress($to);
        
        //调用send方法，执行发送
        $result = $mail -> Send();
        
        if($result){
            return true;
        }else{
            return $mail -> ErrorInfo;
        }
    }
}