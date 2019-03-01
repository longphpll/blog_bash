<?php

namespace app\index\validate;

use think\Validate;

//前台用户验证器
class User extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'    =>    ['规则1','规则2'...]
     *
     * @var array
     */
    protected $regex = [
        /**
         * 移动号码段:139、138、137、136、135、134、150、151、152、157、158、159、182、183、187、188、147, 155, 177
         * 联通号码段:130、131、132、136、185、186、145, 176
         * 电信号码段:133、153、180、189, 173, 181, 170
         */
        'phone' => '/^1(3\d|4[57]|5[0-357-9]|7[0367]|8[0-35-9])\d{8}$/'
    ];

    protected $rule = [
        'phone|手机号码'     => 'require|regex:phone',
        'captcha|图形验证码'  => 'require|alphaNum|length:6',
        'password|短信验证码' => 'require|number|length:6',
        'DID'            => 'require|length:32',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [
        'phone.require'    => '手机必须填写',
        'phone'            => '手机格式错误',
        'captcha.require'  => '图形验证码必须填写',
        'captcha.alphaNum' => '图形验证码必须为字母和数字',
        'captcha.length'   => '图形验证码长度非法',
        'password.require' => '短信验证码必须填写',
        'password.number'  => '短信验证码应为数字',
        'password.length'  => '短信验证码应长度非法',
        'DID.require'      => 'DID必须有',
        'DID.length'       => 'DID长度应为32位',
    ];

    /*定义验证场景*/
    protected $scene = [
        'login' => ['phone', 'captcha', 'password', 'DID'],
    ];
}