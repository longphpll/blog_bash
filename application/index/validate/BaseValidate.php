<?php
/**
 * Created by PhpStorm.
 * User: 7Long
 * Date: 2019/2/13
 * Time: 14:47
 */

namespace app\index\validate;


use think\Validate;

class BaseValidate extends Validate
{
    protected $rule = [
        'program_name|前端程序类型' => 'require|max:60',
        'env_string|个性化环境串'   => 'require|max:60',
        'internet_ip|外网IP地址'  => 'require|ip',
        'cellphone|手机号码'      => ["require", "regex:/^1[3456789]\d{9}$/"],
        'captcha|图形验证码'       => "require|captcha",
        'sms_num|短信验证码'       => 'require',
        'DID'                 => 'require|length:32'
    ];

    protected $message = [
        'cellphone.regex' => "请输入正确的电话号码!",
    ];

    protected $scene = [
        'askForDid'   => ['program_name', 'env_string', 'internet_ip'],
        // 用父类控制器自带的 validate() 方法进行验证
        // $validateRes = $this->validate($this->datas, 'BaseValidate.askForDid');

//        'sendSms'     => ['cellphone', 'captcha'],
        'sendSms'     => ['cellphone'],//测试使用，正式使用时注释这行代码

        'checkDid'    => ['DID'],
        'getUserInfo' => ['DID'],
    ];
}