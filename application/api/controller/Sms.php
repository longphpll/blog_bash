<?php

namespace app\api\controller;


//互亿无线短信接口
class Sms
{
    public $url = "";
    public $appid = "";
    public $appkey = "";

    public function __construct()
    {
        //接口地址
        $this->url    = "http://106.ihuyi.cn/webservice/sms.php?method=Submit";
        $this->appid  = 'C66365011';
        $this->appkey = 'a606c28298e6d55365a37bc2badde88d';
    }

    /**
     * 手机发送短信
     * @param $phone        需要接受信息的手机号码
     * @param $message      需要发送的内容
     */
    public function SendSms($phone, $message)
    {
        //官方提供的转换方式
        $post_data = "account=" . $this->appid . "&password=" . $this->appkey . "&mobile=" . $phone . "&content=" . rawurlencode($message);
//var_dump( $post_data);
//        D:\web\zijiBlog\application\api\controller\Sms.php:33:string 'account=C66365011&password=a606c28298e6d55365a37bc2badde88d&mobile=15814496494&content=%E6%82%A8%E7%9A%84%E9%AA%8C%E8%AF%81%E7%A0%81%E6%98%AF%EF%BC%9Az5bkjl%E3%80%82%E8%AF%B7%E4%B8%8D%E8%A6%81%E6%8A%8A%E9%AA%8C%E8%AF%81%E7%A0%81%E6%B3%84%E9%9C%B2%E7%BB%99%E5%85%B6%E4%BB%96%E4%BA%BA%E3%80%82' (length=291)
//        die();

//        $post_data = "account=" . $this->appid . "&password=" . $this->appkey . "&mobile=" . $phone . "&content=" . $message;
//        echo $post_data;die();
//account=C66365011&password=a606c28298e6d55365a37bc2badde88d&mobile=15814496494&content=您的验证码是：839052。请不要把验证码泄露给其他人。

        //"account=用户名&password=密码&mobile=".$mobile."&content=".rawurlencode("您的验证码是：".$mobile_code."。请不要把验证码泄露给其他人。");

        //有数据后 $post_data，有接口url $this->url 后即可通过curl访问了
        //发送请求，然后在转换接受的 XML 格式
        $arr = $this->xml_to_array($this->post($post_data, $this->url));
//        //把 XML 字符串载入对象中 SimpleXMLElement 对象
//        $xmlObj = simplexml_load_string($this->post($post_data, $this->url));
//        //将 SimpleXMLElement 对象转为 json 字符串,再转为数组
//        $arr = json_decode(json_encode($xmlObj), TRUE);

        //将接口返回的xml格式结果转为数组
//        echo '<pre>';
//        print_r($arr);
//Array
//(
//    [SubmitResult] => Array
//     (
//    [code] => 40722
//    [msg] => 变量内容超过指定的长度  //原因为发送的验证码不能有字母，只能是数字 您的验证码是：839052。请不要把验证码泄露给其他人。
//    [smsid] => 0
//        )
//
//)
        //将二维数组转为一维数组，方便操作
        $resArr          = [];
        $resArr['code']  = $arr['SubmitResult']['code'];
        $resArr['msg']   = $arr['SubmitResult']['msg'];
        $resArr['smsid'] = $arr['SubmitResult']['smsid'];
//        echo '<pre>';
//        print_r($resArr);
//        die();
// Array
//    [code] => 40722
//    [msg] => 40722
//    [smsid] => 0

        return $resArr;
    }

    //请求数据到短信接口，检查环境是否 开启 curl init。
    private function post($post_data, $url)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_NOBODY, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);

        $return_str = curl_exec($curl);

        curl_close($curl);

        return $return_str;

    }

    //将 xml数据转换为数组格式。
    private function xml_to_array($xml)
    {
        $reg = "/<(\w+)[^>]*>([\\x00-\\xFF]*)<\\/\\1>/";
        if (preg_match_all($reg, $xml, $matches)) {
            $count = count($matches[0]);
            for ($i = 0; $i < $count; $i++) {
                $subxml = $matches[2][$i];
                $key    = $matches[1][$i];
                if (preg_match($reg, $subxml)) {
                    $arr[$key] = $this->xml_to_array($subxml);
                } else {
                    $arr[$key] = $subxml;
                }
            }
        }
        return $arr;
    }

}
