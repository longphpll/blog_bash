<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
use base_frame\SmsServer;

//公共语句
header("Access-Control-Allow-Credentials: true");


/**
 * @param $file : 上传源文件（上传谁）
 * @param $dir_path : 上传路径（上传到那里去）
 * @param array $file_type 上传的文件的类型（限制文件类型）
 * @param string $file_size 上文件的大小（限制文件大小）
 * @return bool
 */
function upload_file($file,$dir_path = 'upload/other',
                     $file_type = [],$file_size = '498070'){
    try{
        //分割文件名称
        $arr_name = explode('.',$file['name']);
        //获取文件后缀
        $arr_name_end = end($arr_name);
        if( $file['size'] < $file_size and
            //验证后缀名，$file_type 中是否包含 $arr_name_end 字符串
            in_array( $arr_name_end , $file_type) and
            $file['error'] == 0){

            //判断文件夹是否存在，否：建立文件夹
            if( !file_exists($dir_path) ){
                mkdir($dir_path,0777,true);
            }
            //转换文件名称编码
            $upload_file_name = iconv('utf-8','GBK'
                ,date('YmdHis').$file['name']);
            $upload_res = move_uploaded_file($file['tmp_name'],
                $dir_path.'/'.$upload_file_name);
            if( $upload_res )
                return $dir_path.'/'.$upload_file_name;
            return false;
        }else{
            return false;
        }
    }catch (\Exception $e){
        return $e->getMessage();
    }

}

/**
 * 修改config的函数
 * @param $arr1 配置前缀
 * @param $arr2 数据变量
 * @return bool 返回状态
 */
function setconfig($pat, $rep)
{
    /**
     * 原理就是 打开config配置文件 然后使用正则查找替换 然后在保存文件.
     * 传递的参数为2个数组 前面的为配置 后面的为数值.  正则的匹配为单引号  如果你的是分号 请自行修改为分号
     * $pat[0] = 参数前缀;  例:   default_return_type
    $rep[0] = 要替换的内容;    例:  json
     */
    if (is_array($pat) and is_array($rep)) {
        for ($i = 0; $i < count($pat); $i++) {
            $pats[$i] = '/\'' . $pat[$i] . '\'(.*?),/';
            $reps[$i] = "'". $pat[$i]. "'". "=>" . "'".$rep[$i] ."',";
        }
        $fileurl = "../config/base_frame.php";
        $string = file_get_contents($fileurl); //加载配置文件
        $string = preg_replace($pats, $reps, $string); // 正则查找然后替换
        file_put_contents($fileurl, $string); // 写入配置文件
        return true;
    } else {
        return false;
    }
}

//公共函数
function curlget_http($url)
{                    //curlget提交函数
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, false);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $state = curl_exec($ch);
    curl_close($ch);
    return $state;
}  //得到访问http: 地址时返回的值，可被send_sms()调用
function curlget_https($url)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    //curl_setopt($curl, CURLOPT_POST, false );
    curl_setopt($curl, CURLOPT_HEADER, 1);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);//这个是重点。
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求 不验证证书和hosts
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    $abc = curl_exec($curl);
    curl_close($curl);
    return $abc;
} //得到访问https:地址时返回的值，可被send_sms()调用
/**
 * 发送post请求
 * @param string $url 请求地址
 * @param array $post_data post键值对数据
 * @return string
 */
function send_post($url, $post_data)
{

    $postData = http_build_query($post_data);
    $options = array(
        'http' => array(
            'method' => 'POST',
            'header' => 'Content-type:application/x-www-form-urlencoded',
            'content' => $postData,
            'timeout' => 30    //15 * 60 超时时间（单位:s）
        )
    );
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    return $result;
}//发送post请求，并得到返回值
/**
 * Socket版本
 * 使用方法：
 * $post_string = "app=socket&version=beta";
 * request_by_socket('chajia8.com', '/restServer.php', $post_string);
 */
function request_by_socket($remote_server, $remote_path, $post_string, $port = 80, $timeout = 30)
{
    $socket = fsockopen($remote_server, $port, $errno, $errstr, $timeout);
    if (!$socket) die("$errstr($errno)");
    fwrite($socket, "POST $remote_path HTTP/1.0");
    fwrite($socket, "User-Agent: Socket Example");
    fwrite($socket, "HOST: $remote_server");
    fwrite($socket, "Content-type: application/x-www-form-urlencoded");
    fwrite($socket, "Content-length: " . (strlen($post_string) + 8) . "");
    fwrite($socket, "Accept:*/*");
    fwrite($socket, "");
    fwrite($socket, "mypost=$post_string");
    fwrite($socket, "");
    $header = "";
    while ($str = trim(fgets($socket, 4096))) {
        $header .= $str;
    }

    $data = "";
    while (!feof($socket)) {
        $data .= fgets($socket, 4096);
    }

    return $data;
}

/**
 * Curl版本
 * 使用方法：
 * $post_string = "app=request&version=beta";
 * request_by_curl('http://www.jb51.net/restServer.php', $post_string);
 * 错误时，返回 'fail:'开头的错误描述。
 *
 * $http_address，访问地址
 * $post_array，post数组
 */
function http_server_post($http_address, $post_array)
{
    if (!is_array($post_array)) {
        return 'fail:这里是request_post_by_curl()函数，$post_array参数不为数组';
    }
    $replacement = config('speedConfig.air_http_address');
    $http_address = preg_replace('/ip/i', $replacement, $http_address, 1); //替换第1个ip，忽略大小写
    $replacement = config('speedConfig.air_http_port');
    $http_address = preg_replace('/port/i', $replacement, $http_address, 1); //替换第1个port，忽略大小写
    $post_string = '';
    foreach ($post_array as $key => $item) {
        $post_string .= '&' . $key . '=' . $item;
    }
    $post_string = substr($post_string, 1);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $http_address);
    curl_setopt($ch, CURLOPT_POSTFIELDS, 'mypost=' . $post_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, "jb51.net's CURL Example beta");
    $data = curl_exec($ch);
    curl_close($ch);
    $data = object_to_array(json_decode($data));//转成 stdClass，再转成array
    return $data;
}//http跨域访问，最后起起用的
function http_server_get($http_address, $get_array)
{
    if (!is_array($get_array)) {
        return 'fail:这里是request_get_by_curl()函数，$get_array参数不为数组';
    }
    $replacement = config('speedConfig.air_http_address');
    $http_address = preg_replace('/ip/i', $replacement, $http_address, 1); //替换第1个ip
    $replacement = config('speedConfig.air_http_port');
    $http_address = preg_replace('/port/i', $replacement, $http_address, 1); //替换第1个port
    $get_string = '';
    foreach ($get_array as $key => $item) {
        $get_string .= '&' . $key . '=' . $item;
    }
    $get_string = substr($get_string, 1);
    $ch = curl_init();
    $http_address .= '?' . $get_string;
    curl_setopt($ch, CURLOPT_URL, $http_address);
//    curl_setopt($ch, CURLOPT_POSTFIELDS, 'mypost=' . $get_string);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, "jb51.net's CURL Example beta");
    $data = curl_exec($ch);
    curl_close($ch);
    $data = object_to_array(json_decode($data));//转成 stdClass，再转成array
    return $data;
}//http跨域访问，最后起起用的
function request_post($url = '',$ispost=true, $post_data = array()) {
    if (!is_array($post_data)) {
        return 'fail:这里是request_post()函数，$post_data参数不为数组';
    }
    $replacement = config('speedConfig.air_http_address');
    $url = preg_replace('/ip/i', $replacement, $url, 1); //替换第1个ip，忽略大小写
    $replacement = config('speedConfig.air_http_port');
    $url = preg_replace('/port/i', $replacement, $url, 1); //替换第1个port，忽略大小写

    if (empty($url) || empty($post_data)) {
        return false;
    }

    $o = "";
    foreach ( $post_data as $k => $v )
    {
        $o.= "$k=" . urlencode( $v ). "&" ;
    }
    $post_data = substr($o,0,-1);
    $key=md5(base64_encode($post_data));
    if($ispost){
        $url=$url;
    }else{
        $url = $url.'?'.$post_data;
    }

    $curlPost = 'key='.$key;
    header("Content-type: text/html; charset=utf-8");
    $ch = curl_init();//初始化curl
    curl_setopt($ch, CURLOPT_URL,$url);//抓取指定网页
    curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
    if($ispost){
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
    }
    $data = curl_exec($ch);//运行curl
    curl_close($ch);
    return $data;
}
/**
 * CURL请求（神器）
 * @param $url //请求url地址
 * @param $method //请求方法 get post
 * @param null $postfields post数据数组
 * @param array $headers 请求header信息
 * @param bool|false $debug  调试开启 默认false
 * @return mixed
 */
function httpRequest($url, $method, $postfields = null, $headers = array(), $debug = false) {
    if (!is_array($postfields)) {
        return 'fail:这里是httpRequest()函数，$postfields参数不为数组';
    }
    $replacement = config('speedConfig.air_http_address');
    $url = preg_replace('/ip/i', $replacement, $url, 1); //替换第1个ip，忽略大小写
    $replacement = config('speedConfig.air_http_port');
    $url = preg_replace('/port/i', $replacement, $url, 1); //替换第1个port，忽略大小写

    $method = strtoupper($method);
    $ci = curl_init();
    /* Curl settings */
    curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
    curl_setopt($ci, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.2; WOW64; rv:34.0) Gecko/20100101 Firefox/34.0");
    curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 60); /* 在发起连接前等待的时间，如果设置为0，则无限等待 */
    curl_setopt($ci, CURLOPT_TIMEOUT, 7); /* 设置cURL允许执行的最长秒数 */
    curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
    switch ($method) {
        case "POST":
            curl_setopt($ci, CURLOPT_POST, true);
            if (!empty($postfields)) {
                $tmpdatastr = is_array($postfields) ? http_build_query($postfields) : $postfields;
                curl_setopt($ci, CURLOPT_POSTFIELDS, $tmpdatastr);
            }
            break;
        default:
            curl_setopt($ci, CURLOPT_CUSTOMREQUEST, $method); /* //设置请求方式 */
            break;
    }
    $ssl = preg_match('/^https:\/\//i',$url) ? TRUE : FALSE;
    curl_setopt($ci, CURLOPT_URL, $url);
    if($ssl){
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求 不验证证书和hosts
        curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, FALSE); // 不从证书中检查SSL加密算法是否存在
    }
    //curl_setopt($ci, CURLOPT_HEADER, true); /*启用时会将头文件的信息作为数据流输出*/
    curl_setopt($ci, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ci, CURLOPT_MAXREDIRS, 2);/*指定最多的HTTP重定向的数量，这个选项是和CURLOPT_FOLLOWLOCATION一起使用的*/
    curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ci, CURLINFO_HEADER_OUT, true);
    /*curl_setopt($ci, CURLOPT_COOKIE, $Cookiestr); * *COOKIE带过去** */
    $response = curl_exec($ci);
    $requestinfo = curl_getinfo($ci);
    $http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
    if ($debug) {
        echo "=====post data======\r\n";
        var_dump($postfields);
        echo "=====info===== \r\n";
        print_r($requestinfo);
        echo "=====response=====\r\n";
        print_r($response);
    }
    curl_close($ci);

    return object_to_array(json_decode($response));
    //return array($http_code, $response,$requestinfo);
}

/**
 * stdClass对象转array
 */
function object_to_array($obj)
{
    $_arr = is_object($obj) ? get_object_vars($obj) : $obj;
    foreach ($_arr as $key => $val) {
        $val = (is_array($val) || is_object($val)) ? object_to_array($val) : $val;
        $arr[$key] = $val;
    }
    return $arr;
}

function random_string($length = 8)
{
    // 密码字符集，可任意添加你需要的字符
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $randWord = '';
    for ($i = 0; $i < $length; $i++) {
        $randWord .= $chars[mt_rand(0, strlen($chars) - 1)];
    }
    return $randWord;
}//返回指定字符集、指定长度的随机字符串
function random_number($length = 8)
{
    // 密码字符集，可任意添加你需要的字符
    $chars = '0123456789';
    $randWord = '';
    for ($i = 0; $i < $length; $i++) {
        $randWord .= $chars[mt_rand(0, strlen($chars) - 1)];
    }
    return $randWord;
}//返回指定字符集、指定长度的随机字符串

/**
 * 产生指定目录，深度不限。
 * 注意，只有返回 true 才表示顺利执行
 * 如果有则罢了，如果没有则建立它，可以不限建立子目录的深度
 * 对$dir的要求：
 * 0、可接受“d:\temp”和“c:\temp\”两种形式的目录描述，其中“temp”为最后一级目录名；
 * 1、目录分隔符可以是“\”或“/”，两都可以同时用，本函数会将它都转成“/”，在windows中标准为“\”但也可以用“/”；
 * 2、两个（及以上）连续的分隔符视为不合法；
 * 3、最后的分隔符将被忽略；
 * 4、注意，$dir不一定被当作一个绝对路径来分析，它可以是从当前目录（如public）或当前驱动器开始的相对路径，只要PHP的目录判断函数认可；
 * 5、考虑到可能用于linux，绝对路径名的最开始是“/”；
 * 6、在windows下时，绝对路径名是从“c:\……”开始的，有冒号；
 * 7、不允许有“/d:”情况出现在最开始；
 *
 * 返回值：
 * 1、$dir中有空白字符；
 * 2、$dir长度为零；
 * 3、$dir有两个连续“/”或“\”出现；
 * 4、无法创建一个与文件名同名的文件夹；
 * 5、创建目录的权限不够；
 *
 * @param $dir
 * @return bool|string //bool只能是true，注意在判断时要用 create_dir()===true
 */
function create_dir($dir)
{

    //$dir中不能有不可见字符
    $regex = '/\s/';
    if (preg_match($regex, $dir)) return '1.路径中含不可见字符';
    //$dir长度应大于0
    if (strlen($dir) == 0) return '2.路径字符串长度应大于0';
    //将“\”转换成“/”
    $change_array = [
        '\\' => '/',
    ];
    $dir = strtr($dir, $change_array);
    //将最后一个“/”去掉
    if (substr($dir, strlen($dir) - 1) == '/') {
        $dir = substr($dir, 0, strlen($dir) - 1);
    }
    //将第1个“/”暂时藏起来
    $hide = false;
    if (substr($dir, 0, 1) === '/') {
        $hide = true;
        $dir = substr($dir, 1);//去掉第1个“/”
    }
    $dir_array = explode('/', $dir);
    //将第1个“/”还回来
    if ($hide) {
        $dir_array[0] = '/' . $dir_array[0];
    }
    $dir_temp = '';
    foreach ($dir_array as $key => $item) {
        if ($item === '') {
            return '3.路径字符串出现两个连接的“/”或“\”'; //说明有两个连续的“/”出现，这是不符合约定的
            break;
        }
        if ($key === 0) {
            $dir_temp = $item;
        } else {
            $dir_temp .= '/' . $item;
        }
        if (file_exists($dir_temp)) {
            if (is_dir($dir_temp)) {
                continue;
            } else {
                return '4.无法创建与一个文件同名的文件夹'; //无法创建与一个文件同名的文件夹
            }
        } else {
            if (mkdir($dir_temp)) {
                continue;
            } else {
                return '5.创建目录失败，可能权限不够'; //可能是创建目录的权限不够
            }
        }
    }
    return true; //只返回 true才表示成功
}//根据“路径”参数，来创建目录，级数不限，本函数属于通用自定义函数

function xml_encode($data_array, $charset = 'GB2312', $root = 'ExchangeData')
{
    //这是要调用联网中心要用到的函数
    $xml = '<?xml version="1.0" encoding="' . $charset . '"?>';
    $xml .= "<{$root}>";
    $xml .= array_to_xml($data_array);
    $xml .= "</{$root}>";
    return $xml;
}

function xml_decode($xml, $root = 'ExchangeData')
{
    //这是要调用联网中心要用到的函数
    $search = '/<(' . $root . ')>(.*)<\/\s*?\\1\s*?>/s';
    $array = array();
    if (preg_match($search, $xml, $matches)) {
        $array = xml_to_array($matches[2]);
    }
    return $array;
}

function array_to_xml($array)
{
    if (is_object($array)) {
        $array = get_object_vars($array);
    }
    $xml = '';
    foreach ($array as $key => $value) {
        $_tag = $key;
        $_id = null;
        if (is_numeric($key)) {
            $_tag = 'item';
            $_id = ' id="' . $key . '"';
        }
        $xml .= "<{$_tag}{$_id}>";
        $xml .= (is_array($value) || is_object($value)) ? array_to_xml($value) : htmlentities($value);
        $xml .= "</{$_tag}>";
    }
    return $xml;
}//被xml_encode()调用
function xml_to_array($xml)
{
    $search = '/<(\w+)\s*?(?:[^\/>]*)\s*(?:\/>|>(.*?)<\/\s*?\\1\s*?>)/s';
    $array = array();
    if (preg_match_all($search, $xml, $matches)) {
        foreach ($matches[1] as $i => $key) {
            $value = $matches[2][$i];
            if (preg_match_all($search, $value, $_matches)) {
                $array[$key] = xml_to_array($value);
            } else {
                if ('ITEM' == strtoupper($key)) {
                    $array[] = html_entity_decode($value);
                } else {
                    $array[$key] = html_entity_decode($value);
                }
            }
        }
    }
    return $array;
}//被xml_decode()调用

//------------------------十六进制 与 汉字 的互转-----------------------
/**
 * 从 十六进制字符串（基于GB2312）ASCII码 转换为 汉字（UTF-8）串
 * @param $str_hex
 * @return bool|string //如果成功，返回utf-8字符串，失败返回false
 */
function hexToChs($str_hex){
    //判断是否为十进制字符串
    if(!preg_match('/^[0-9A-Fa-f]+$/',$str_hex)){
        return false;
    }
    //正式运算
    $len=strlen($str_hex);
    if($len%4 !== 0){
        return false; //只能将十进制转换GB2313编码的纯汉字符，一个汉字占4个十六进制位，结果为utf-8编码
    }
    $chs_array=array();
    for($i=0;$i<$len;$i+=4){
        $chs_array[]=substr($str_hex,$i,4);
    }
    $str_utf8='';
    foreach($chs_array as $key=>$chs_single){
        $a=substr($chs_single,0,2);
        $b=substr($chs_single,2,2);;
        $char1=chr(hexdec($a));
        $char2=chr(hexdec($b));
        $char=$char1.$char2;
        $encode=mb_detect_encoding($char, array("ASCII",'UTF-8',"GB2312","GBK",'BIG5'),true);
        //if($encode!='EUC-CN' and $encode!='ASCII' and $encode!='GB2312'){
        //return false; //说明输入了非GB2312字符
        //}
        $str_utf8.=iconv('gb2312','utf-8',$char);
        //$str_utf8.=$char;
    }
    //dump($str_utf8);
    //$encode=mb_detect_encoding($str_utf8, array("ASCII",'UTF-8',"GB2312","GBK",'BIG5'),true);
    //dump($encode);
    //if($encode=='EUC-CN'){ //'EUC-CN'就是'GB2312'
//        $str_utf8.=iconv('gb2312','utf-8',$str_utf8);
//        echo '<br>***<br>';
//    }
    return trim($str_utf8);
}
/**
 * 从 汉字（UTF-8） 转换为 十六进制字符串（基于GB2312）串ASCII码
 * @param $str_chs     //必须全部是utf-8的汉字
 * @return bool|string //如果成功，返回十六进制字符串，失败返回false
 */
function chsToHex($str_chs){
    //判断是否全部为中文
    //$check=Validate::is($str_chs,'chs');
    //if($check===false){
    //    return false;
    //}
    $str_gb2312=iconv('utf-8','gb2312',$str_chs);
    $byte_array=str_split($str_gb2312);
    $byte_ord_hex_array=array();
    foreach($byte_array as $item){
        $byte_ord_hex_array[]=dechex(ord($item));
    }
    return strtoupper(implode('',$byte_ord_hex_array));
}
//---------------------------------------------------------------------
/*===============2019年2月12日 7Long================*/
/**
 * 返回成功
 * @param null $data 数据
 * @param int $code 状态码
 * @param string $msg 返回提示消息
 * @return \think\response\Json
 */
function success($data = null, $code = 1, $msg = null)
{
    if (empty($msg)){
        return json(["code" => $code, "msg" => $data]);
    }
    return json(["code" => $code, "msg" => $msg, "data" => $data]);
}

/**
 * 返回失败
 * @param null $data 数据
 * @param int $code 状态码
 * @param string $msg 返回提示消息
 * @return \think\response\Json
 */
function error($data = null, $code = 0, $msg = null)
{
    if (empty($msg)) {
        return json(["code" => $code, "msg" => $data]);
    }
    return json(["code" => $code, "msg" => $msg, "data" => $data]);
}

/**
 * 阿里大于的短信平台发送短信
 * @param $cellphone string 电话号码
 * @param $session_name string session名称
 * @return bool|string
 */
function send_sms($cellphone, $session_name) // 注意，返回值有两种可能，1、true；2、错误描述。所以，主调程序要注意分析处理的方法
{
    // 新增一个对发送短信时间的判断
    if (!empty(session('lastSendSms'))) {
        if (abs(time() - session('lastSendSms')) < config('baseConfig.interval')) {
            return '小于最小发送间隔[' . config('baseConfig.interval') . ']';
        } else {
            session('lastSendSms', time());
        }
    } else {
        session('lastSendSms', time());
    }
    $date_time = time();
    $smsDateTime = date('Y-m-d H:i:s', $date_time);  // 短信发送时间
    $smsMessageId = rand(1000000000, 999999999);
    $sms = SmsServer::getInstance();
    $sms_verificationNum = random_number(6);
    $sms->sendVerifyCode($cellphone, $sms_verificationNum);
    switch ($sms->okay) {
        case '1':
            // 发送成功,设置 verify_send_sms Session的值
            session($session_name, [                   //留给login接口用了，详见紧下说明：
                'sms_num' => $sms_verificationNum,     //发送给云短信的“短信中的数字部分” ，只给login验证用的，不作为登录凭证
                'smsDateTime' => $smsDateTime,         //判断60秒钟的 ，只给login作为判断超时用的，不作为登录凭证
                'smsMessageId' => $smsMessageId,       //写base_user_login_log中login_time的 ，只给login作为写base_user_login_log表用的，不作为登录凭证
                'cellphone' => $cellphone,]);
            $result = true;
            break;
        case '0':
            $result = $sms->error;
            break;
        default:
            $result = $sms->error;
            break;
    }
    return $result;
}

/**
 * 去掉不可见字符
 * @param $string
 * @return null|string|string[]
 */
function cut_invisible($string)
{
    return preg_replace('/\s/', '', $string); //正则中\s表示“任意空白字符”，trim()只是首尾去空
}

/**
 * @param array $data 数组
 * @param array $keys 需要删除的key数组
 * @return mixed
 */
function array_unset($data, $keys)
{
    foreach ($keys as $key) {
        foreach ($data as &$item)
        {
            if(!array_key_exists($key, $item))
            {
                continue;
            }
            unset($item[$key]);
        }
    }
    return $data;
}