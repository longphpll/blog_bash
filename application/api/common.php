<?php

//获得随机生成的6位密码,调用短信接口平台时使用
function getRandomPwd()
{
    //测试时用这个字符串
    $str = '0123456789abcdefghijklmnopqrstuvwxyz';
    //随机产生的验证码不能带字母，只能是数字，否则会报错
//Array
//(
//    [SubmitResult] => Array
//     (
//    [code] => 40722
//    [msg] => 变量内容超过指定的长度
//    [smsid] => 0
//        )
//
//)

    //正式时用 $str  = '0123456789' 字符串，还要记得要将 User 控制器下的  doLogin()方法里
//    $data = [
//        'phone'    => 15814496494,
//        'password' => 123456,
//    ];
//    session('userInfo', $data); 几行代码注释掉*******************************

//    $str  = '0123456789';
    $code = '';
    for ($i = 0; strlen($code) < 6; $i++) {
        $text = $str[mt_rand(0, strlen($str) - 1)];
        //去除重复的
        if (strpos($code, $text) === false) {
            $code .= $text;
        }
    }
    return $code;
}
