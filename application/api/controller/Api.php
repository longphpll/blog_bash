<?php

namespace app\api\controller;

use think\Controller;
use think\Db;
use think\Request;
use app\api\controller\Sms;

class Api extends Controller
{
    //查询域名是否可用 API
    public function domain(Request $request)
    {
        //接收参数
        $domain = $request->param('domain');

        $url = 'http://panda.www.net.cn/cgi-bin/check.cgi?area_domain=' . $domain;

        //方式一
//        $data = file_get_contents($url,'rb');
//        halt($data);

        //方式二：最好用 curl 方式
        $ch = curl_init(); //初始化一个CURL对象

        //设置你所需要抓取的URL
        curl_setopt($ch, CURLOPT_URL, $url);

        // 设置header 启用时会将头文件的信息作为数据流输出。
        //一般设置为0,不需要头部信息
        curl_setopt($ch, CURLOPT_HEADER, 0);

        // 设置cURL 参数，要求结果保存到字符串中还是输出到屏幕上。
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //设置curl参数，要求结果是否输出到屏幕上，
        //为true的时候是保存到字符串
        //为false的时候是输出到屏幕上

        // 运行cURL，请求网页
        $data = curl_exec($ch);

        // 关闭URL请求
        curl_close($ch);

//        //simplexml_load_string() 函数把 XML 字符串载入对象中。
        $xml  = simplexml_load_string($data);
        $data = json_decode(json_encode($xml), TRUE);

//        halt($data);
//array(3) {
//        ["returncode"] => string(3) "200"
//        ["key"] => string(10) "fdasfa.com"
//        ["original"] => string(30) "210 : Domain name is available"
//}
        //获取状态码 210代表可以注册 211代表不可以注册
        $code    = $data['original']; //string(30) "210 : Domain name is available"
        $code    = strchr($code, ':', true);
        $message = $code == 210 ? '可以注册' : '不可以注册';

        $newData['code']    = $code == 210 ? 1 : 0;//1可以注册，0不可以注册
        $newData['key']     = $data['key'];
        $newData['message'] = $message;
        return json_encode($newData);

//        halt($data);
//array(3) {
//        ["returncode"] => string(3) "200"
//        ["key"] => string(10) "fdasfa.com"
//        ["original"] => string(12) "可以注册"
//}

//
// 做微信开发的时候，项目中需要用PHP去请求微信相关接口。刚开始使用的是file_get_contents这个函数，后来听朋友说最好用curl。自己尝试了下，也能成功请求微信的接口。这两个有什么区别呢？抱着好奇心查阅了相关资料后，才知道他们之间确实有很大的不同。
//
//1.fopen /file_get_contents 每次请求都会重新做DNS查询，并不对 DNS信息进行缓存。但是CURL会自动对DNS信息进行缓存。对同一域名下的网页或者图片的请求只需要一次DNS查询。这大大减少了DNS查询的次数。所以CURL的性能比fopen /file_get_contents 好很多。
//
//2.fopen /file_get_contents 在请求HTTP时，使用的是http_fopen_wrapper，不会keeplive。而curl却可以。这样在多次请求多个链接时，curl效率会好一些。
//
//3.fopen / file_get_contents 函数会受到php.ini文件中allow_url_open选项配置的影响。如果该配置关闭了，则该函数也就失效了。而curl不受该配置的影响。
//
//4.curl 可以模拟多种请求，例如：POST数据，表单提交等，用户可以按照自己的需求来定制请求。而fopen / file_get_contents只能使用get方式获取数据。
//file_get_contents 获取远程文件时会把结果都存在一个字符串中 fiels函数则会储存成数组形式
//
//由此可知curl在性能、速度、稳定性上都要优于file_get_contents，所以建议以后使用curl库进行网络请求。
    }

    //当点击 短信获取登录密码 按钮时，通过ajax访问该方法，
    //然后该方法再调用 Sms控制器里的 互亿无线短信接口，获取结果(数组)
    public function smsSend()
    {
        //通过 ajax 获取用户输入的手机号码
        $phone = \request()->post('phone');
//        $phone = '15814496494';

        //调用公共函数获取随机的登录密码，需注意只能为数字，不能为字母，否则短信接口平台会返回 变量内容超过指定的长度 错误
        $randomPwd = getRandomPwd();
        //发送给用户的消息
        $message = '您的验证码是：' . $randomPwd . '。请不要把验证码泄露给其他人。';


        //实例化 Sms 短信平台类
        $smsObj = new Sms();
        //调用短信平台接口
        $resArr = $smsObj->SendSms($phone, $message);
//        Array
//        (
//            [SubmitResult] => Array
//            (
//            [code] => 2
//            [msg] => 提交成功
//         )

        $date_time   = time();
        $smsDateTime = date('Y-m-d H:i:s', $date_time);  // 短信发送时间

        //短信发送成功将一些信息存入 session 中,登录时会用的到
        if ($resArr['code'] == 2) {
            $data = [
                'phone'       => $phone,//用户手机
                'password'    => $randomPwd,//短信平台发送给用户的短信验证码
                'smsDateTime' => $smsDateTime,  //只给login作为判断短信验证码是否超时用的，不作为登录凭证
            ];
            session('userInf', $data);

            //上面session已经保存了该用户的手机和短信平台发给用户的短信验证码
            //为什么下面还要将手机和短信验证码保存到表 log_sms 里呢，
            //当输入的手机号不是当前操作人员手机号时需要知道该手机号的短信验证码是多少
            //这是将平台发送的短信验证码保存到表 log_sms 里方便查询，以便能够登录就去
            $res = Db::table('log_sms')->insert($data);
            if (!$res) {
                $res['code'] = 0;
                $res['msg']  = '添加数据失败';
                return json_encode($res);
            }
        }

        //将平台返回的结果以json字符串返给ajax
        return json_encode($resArr);
    }
}
