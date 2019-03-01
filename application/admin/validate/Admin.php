<?php

namespace app\admin\validate;

use think\Validate;

class Admin extends Validate
{
    /**
     * 定义验证规则
     * 格式：'字段名'    =>    ['规则1','规则2'...]
     *
     * @var array
     */
    protected $regex = [
        //邮箱正则要求:
        //@之前必须有内容且只能是字母（大小写）、数字、下划线(_)、减号（-）、点（.）
        //@和最后一个点（.）之间必须有内容且只能是字母（大小写）、数字、点（.）、减号（-），且两个点不能挨着
        //最后一个点（.）之后必须有内容且内容只能是字母（大小写）、数字且长度为大于等于2个字节，小于等于6个字节
        'email' => '/^[\w.-]+@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)?\.(com|net|org|cn)$/',

        /**
         * 移动号码段:139、138、137、136、135、134、150、151、152、157、158、159、182、183、187、188、147, 155, 177
         * 联通号码段:130、131、132、136、185、186、145, 176
         * 电信号码段:133、153、180、189, 173, 181, 170
         */
        'phone' => '/^1(3\d|4[57]|5[0-357-9]|7[0367]|8[0-35-9])\d{8}$/'
    ];

    protected $rule = [
        'username' => 'require|length:2,100|unique:admin',
        'pwd'      => 'require|min:6',
        'repwd'    => 'require|confirm:pwd',
        'email'    => 'require|regex:email',
        'phone'    => 'require|regex:phone',
        'captcha'  => 'require|alphaNum|length:6',
    ];

    /**
     * 定义错误信息
     * 格式：'字段名.规则名'    =>    '错误信息'
     *
     * @var array
     */
    protected $message = [
        'username.require' => '管理员用户名不能为空',
        'username.length'  => '管理员用户名长度非法(2-100)',
        'username.unique'  => '该用户名已被占用,请重新输入',
        'pwd.require'      => '密码必须填写',
        'pwd.min'          => '密码最短是6位',
        'repwd.require'    => '确认密码不能为空',
        'repwd.confirm'    => '确认密码与密码不一致,请重新输入',
        'email.require'    => '邮箱必须填写',
        'email.regex'      => '邮箱格式非法',
        'phone.require'    => '手机必须填写',
        'phone.regex'      => '手机格式错误',
        'captcha.require'  => '图形验证码必须填写',
        'captcha.alphaNum' => '图形验证码必须为字母和数字',
        'captcha.length'   => '图形验证码长度非法',
    ];
}