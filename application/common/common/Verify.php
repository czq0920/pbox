<?php
/*
 * 封装的用户名、密码、邮箱的验证规则类
 */
namespace app\common\common;
use think\Validate;

/**
 * Class Verify所有验证器都必须继承该类
 * @package app\view
 */
class Verify extends validate
{   
    private $parentError = array();
    //定义一个方法，遍历错误数组的信息
    public function showError()
    {
        $error_mess = '';
        foreach ($this->parentError as $row)
        {
            $error_mess .= $row.'<br>';
        }
        return $error_mess;
    }
    //验证用户名是否符合规则
    //参数1：需要验证的用户名
    //参数2：最少多少个字符长度
    //参数3：最多多少个字符长度
    public function verifyUser($username,$param)
    {
        $param = explode(',',$param);
        $min = $param[0];
        $max = $param[1];
        //dump($username).'hr'.dump($min).'hr'.dump($max);die;
        //定义规则
        //规则：6-30位字母、数字、_组合，以字母开头
        //从开始到结束共6-30位
        $reg = '/^[a-zA-Z]\w{'.($min-1).','.($max-1).'}$/';
        preg_match($reg,$username,$match);

        if(!$match){
            //说明不符合规则
            $this -> parentError[] = '<font color="red">用户名必须是：'.$min.'-'.$max.'位字母、数字、_组合，以字母开头</font>';
            //阻止继续往下执行
            return false;
        }else{
            return true;
        }
    }
    //验证真实姓名是否符合规则
    //参数1：需要验证的用户名
    //参数2：最少多少个字符长度
    //参数3：最多多少个字符长度
    public function verifyRealName($username,$param)
    {
        $param = explode(',',$param);
        $min = $param[0];
        $max = $param[1];

        //定义规则
        //规则：6-30位字母、数字、_组合，以字母开头
        //从开始到结束共6-30位
        $reg = '/^[a-zA-Z\x{4e00}-\x{9fa5}]{'.($min).','.($max).'}$/u';
        preg_match($reg,$username,$match);

        if(!$match){
            //说明不符合规则
            $this -> parentError[] = '<font color="red">真实姓名必须是：'.$min.'-'.$max.'位汉子、字母组合</font>';
            //阻止继续往下执行
            return false;
        }else{
            return true;
        }
    }
    //验证密码是否符合规则
    public function verifyPass($password,$param)
    {
        $param = explode(',',$param);
        $min = $param[0];
        $max = $param[1];
        $reg = '/^[a-zA-Z\d~!@#$%\^&\*\(\)\_\+\{\}\|;:\"\'?<>\.]{'.$min.','.$max.'}$/';
        preg_match($reg, $password,$match);
        if(!$match){
            //说明不符合规则
            $this -> parentError[] = '<font color="red">密码必须是：'.$min.'-'.$max.'位字母、数字或符号</font>';
            //阻止继续往下执行
            return false;
        }else{
            return true;
        }
    }
    //验证邮箱是否符合规则
    public function verifyEmail($email)
    {
        $reg = '/^[\w\.-]+@[a-zA-Z\d]+(\.[a-zA-Z]+)?\.[a-zA-Z]{1,3}/';
        
        preg_match($reg, $email,$match);
        if(!$match){
            //说明不符合规则
            $this -> parentError[] = '<font color="red">邮箱格式不正确</font>';
            //阻止继续往下执行
            return false;
        }else{
            return true;
        }
    }
    //验证手机号码是否符合规则
    public function verifyPhone($phone)
    {
        $reg = '/^1[34578]\d{9}$/';
    
        preg_match($reg, $phone,$match);
        if(!$match){
            //说明不符合规则
            $this -> parentError[] = '<font color="red">手机格式不正确</font>';
            //阻止继续往下执行
            return false;
        }else{
            return true;
        }
    }

}