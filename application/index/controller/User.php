<?php

namespace app\index\controller;

use app\common\controller\IndexBase;
use think\captcha\Captcha;
use think\Session;
use app\index\model\User as userModel;
use think\Db;

//前台用户控制器
class User extends IndexBase
{
    //定义日志文件的名称
    private $type_dir = "login.txt";

    /**
     * 前置操作: 在访问某些方法之前,优先需要访问的内容
     * 访问 User 控制器的方法之前,优先访问 checkAdmin 方法;
     * 但是 login() 方法和 doLogin() 方法除外
     * except 排除    only   只包含
     * 注意: 前置操作中的排除或包含的方法名,要写成小写
     */
    protected $beforeActionList = [
        'checkDid' => ['except' => 'login,dologin,verify,logout'],
        //除了 login,verify 和 delogin 方法外,其它都执行 checkAdmin() 方法
    ];

    protected function checkDid()
    {
        $jsonObj = $this->check_did(); //返回 \think\response\Json 对象实例
        $arr     = json_decode($jsonObj->getContent(), true);
        if ($arr['code'] != 1) {
            $this->error($arr['msg'], url('index/user/login'));
        }
    }

    //用户列表
    public function index()
    {
        //查询用户表
        $list = db('user')->field('id,username,created')
            ->order('created DESC')->select();
        $this->assign('list', $list);
        return $this->fetch();
    }

    //生成图形验证码返回给前端
    public function verify()
    {
        $config  = [
            // 验证码字体大小
//            'fontSize' => 80,
            // 验证码位数
            'length'   => 6,
            // 是否画混淆曲线
            'useCurve' => false,
            // 关闭验证码杂点
            'useNoise' => false,
        ];
        $captcha = new Captcha($config);
        return $captcha->entry();
    }

    //如果验证码的生成是调用Captcha类操作 $captcha = new Captcha(); return $captcha->entry();
    //完成的，那么验证码的验证如下方法验证
    function checkCaptcha()
    {
        //这里是前端通过ajax传来的用户输入的图形验证码值来验证用户输入的图形验证码是否正确
        //当用户提交form表单后,后台也要验证,所以一定要将 reset 设置为 false

        //接收参数
        $val = request()->param('captchaVal');

        //reset	验证成功后是否重置	默认为 true，如果为true只能验证一次
        $captcha        = new Captcha();
        $captcha->reset = false;
        $bool           = $captcha->check($val);

        $list = [];
        if (!$bool) {
            $list['code'] = 0;
            $list['msg']  = '图形验证码错误,请重新输入';
        } else {
            $list['code'] = 1;
            $list['msg']  = '图形验证码正确';
        }

        return json_encode($list);
    }

    //用户登陆页面
    public function login()
    {
        return $this->fetch();
    }

    /**
     * 用户登录
     * 1.通过DID查询redis中的用户信息，如果不存在说明之前没有登录过，进行初次登录的操作
     * 2.查询DID对应的 USER_INFO 中cellphone是否一致，如果不一致说明了切换了用户
     * 3.初次登录，验证手机号是否注册、图形验证码、短信验证码
     * 4.登录成功，将KEY=手机号 VALUE=DID 和  KEY=DID VALUE=USER_INFO 都存入redis中
     */
//    登录的时候要验证如下(先要注册，才能登录)
//    验证手机号是否注册
//    短信验证码的验证
//    短信验证码 验证时间是否超时
//    如果以上都通过那么就产生登录凭证
    public function doLogin()
    {

        //调用公共函数,去掉空格
        foreach ($this->datas as $k => $v) {
            $this->datas[$k] = cut_invisible($v);
        }

        //参数验证(调用controller控制器自身的 validate() 方法)
        $validateRes = $this->validate($this->datas, 'User.login');

        //日志操作(如果用户输入的信息没有通过验证 写入日志文件)
        $log_txt       = new LogController();
        $log_condition = '电话号码：' . $this->datas['phone'] . "\r\n";
        $log_condition .= '短信验证码：' . $this->datas['password'] . "\r\n";
        $log_condition .= '图形验证码：' . $this->datas['captcha'] . "\r\n";
        if ($validateRes !== true) { // validate验证失败，写错误日志并返回
            $log_condition .= 'error_msg:' . $validateRes . "\r\n";
            $log_txt->log($this->type_dir, $log_condition);
            $this->error($validateRes);
//            return error($validateRes);
        };

        /*测试时需注释，正式使用时打开*/
        //验证图形验证码
        //reset	验证成功后是否重置	默认为 true，如果为true只能验证一次
        $captcha = new Captcha();
        $captcha->reset = false;
        //用户输入的图形验证码
        $captchaVal = $this->datas['captcha'];
        //验证
        $bool = $captcha->check($captchaVal);
        if (!$bool) {
            $this->error('图形验证码输入错误,请重新输入');
        }

        //-----------------------------------------------------------------------------------
        //短信平台只给了10条免费短信，为了调试过程中不浪费
        //所以这里模拟短信发送成功后将手机号和短信验证码存到session，然后和用户输入的短信验证码进行匹配
        $data = [
            'phone'    => 15814496494, //如果要注册新号码请更改这里
            'password' => 123456,
        ];
        session('userInf', $data);
        //当正式使用时需注销掉上面的代码，
        //api 控制器下的 smsSend()方法 如果发送短信成功
        //会将用户的手机号和发送给用户的短信验证码保存到session里，同时再保存到表 sms_user 里
        //------------------------------------------------------------------------------------

        //最重要的一步 短信验证码 的验证
        //先验证用户输入的登录密码(短信验证码)是否正确，如果正确后再验证短信验证码是否超时

        //如果用户输入的短信验证码 != 短信平台发送给用户的短信验证码，说明用户输入的短信验证码时错误的
        if ($this->datas['password'] != session('userInf')['password']) {
            $log_condition .= 'error_msg:' . '短信验证码错误!' . "\r\n";
            $log_txt->log($this->type_dir, $log_condition);
            $this->error('短信验证码错误');
            //如果是写接口就如下面格式返回
//            return error('短信验证码错误');
        }

        //验证 短信验证码 是否超时
        //现在的时间戳-短信平台发送给用户的时间戳如果 > 300s 说明用户输入的短信超时了
        date_default_timezone_set('ETC/GMT-8');//切换到中国区

        //获取当前的时间戳
        $nowDateTime       = date('Y-m-d H:i:s');//2019-02-27 10:29:27
        $nowDateTime_stamp = (new \DateTime($nowDateTime))->getTimestamp();

        //为了测试这里临时加上session设置 正式使用时注释这行代码
        $smsDateTime_stamp = date('Y-m-d H:i:s', $nowDateTime_stamp + 300);//正式使用时注释这行代码
        session('userInfo', ['smsDateTime' => $smsDateTime_stamp]);  //正式使用时注释这行代码

        $smsDateTime_stamp = (new \DateTime(session('userInfo')['smsDateTime']))->getTimestamp();
        //echo '短信平台发送短信验证码成功时的时间戳为：', $smsDateTime_stamp,'<br>';//短信平台发送短信验证码时的时间戳 1551237475 1551237475 值不变 //正式使用时注释这行代码
        $diffSecond = abs($nowDateTime_stamp - $smsDateTime_stamp);
        if ($diffSecond > 300) {
            $log_condition .= 'error_msg:' . '短信验证码超时!' . "\r\n";
            $log_txt->log($this->type_dir, $log_condition);
            return error('短信验证码超时!');     //当 当前时间戳为：1551237778 程序报 {"code":0,"msg":"短信验证码超时!"}
        }

        //如果以上都通过那么就产生登录凭证(将用户保存到数据库表中)
        //将用户保存到数据库表中
        //先从表中查找有没有该记录，如果没有说明首次登录和注册，如果有说明已注册，只需更新登录时间
        $res = Db::table('tedu_user')
            ->where(['phone' => session('userInf')['phone']])
            ->find();

        //如果从表中没有查询该手机用户，说明首次登录和注册
        if (!$res) {
            //通过模型完成注册,创建时间 和 登录时间应一致 created log_time
            //create方法的第二个参数可以传入允许写入的字段列表(传入true则表示仅允许写入数据表定义的字段数据)
            try {
                $res = userModel::create(session('userInf'), true);
                //INSERT INTO `tedu_user` (`phone` , `log_time` , `created`) VALUES ('15814496494' , 1551097732 , 1551097732)
            } catch (\Exception $e) {
                return error($e->getTrace(), 0, $e->getMessage());
            }
        } else {
            //如果从表中查询到该用户说明已经注册过，那么直接修改登录时间 log_time 即可
            try {
                //通过模型完成更新，更新用户的登录时间
                $res = userModel::update($res, ['phone' => $res['phone']], true);
                //UPDATE `tedu_user` SET `phone`='15814496494',`created`=1551097567,`log_time`=1551097666 WHERE `id` = 3
            } catch (\Exception $e) {
                return error($e->getTrace(), 0, $e->getMessage());
            }
        }

        $user_array = [
            'id'       => $res->id,
            'username' => $res->phone,
            'created'  => $res->created,
            'log_time' => date('Y-m-d H:i:s', $res->log_time),//登录时间
        ];

        $certificate_array = $this->create_certificate($user_array);

        // 写日志并返回
        Session('userInf', null);//这是在 send_sms() 中写下的 session，完成登录后销毁。
        $log_condition .= var_export($certificate_array, true) . "\r\n";
        $log_txt->log($this->type_dir, $log_condition);
//        //postman 使用时
//        if ($certificate_array['code'] = 1) {
////            return success($certificate_array['msg']);
//            return success($certificate_array);
//        } else {
//            return error($certificate_array['msg']);
//        }
        $this->success('登陆成功', url('index/index/index'));
    }

    //如果以上都通过那么就产生登录凭证
    private function create_certificate($user_array)
    {
        //将用户信息写入 session中
        session('user', $user_array);
        //2、登录保持，在服务器端 redis 中以带有效期的 string 数据类型对用户登录状态进行记录；
        //将用户信息写入 redis //分别为：DID 对应的用户信息，电话号码对应的 DID
//        $this->redis->set('TRF_DID_' . $this->datas['DID'], $user_array, 3);//3s
        $this->redis->set('TRF_DID_' . $this->datas['DID'], $user_array, 1800);//30分钟
        //登录成功后如果did超过30分钟，did过期
        $this->redis->set('TRF_PHONE_' . $user_array['username'], $this->datas['DID'], 1800);
        //主要和前端传来的did比较,如果 != 说明异地登录

        //模拟未登录(正式使用时注释这行代码)
//        $this->redis->rm('TRF_DID_' . $this->datas['DID']);
//        $this->redis->rm('TRF_PHONE_' . $user_array['username']);

        $result_array = [
            'code'    => 1,
            'msg'     => '登录成功,顺利得到登录凭证，USER_INFO已写入redis',
            'session' => ['USER_INFO' => $user_array],
        ];
        return $result_array;
    }

    //删除用户
    public function delete()
    {
        //当前用户的id
        $id = input('param.id');

//        数据库的方式删除
//        $res = db('user')->delete($id);

//        模型方式删除(有两种方式)

//        模型方式删除(第一种方式)调用普通方法用对象调用
//        $res = model('user')->doDelete($id);

//        模型方式删除(第二种方式)需要起别名 调用静态方法用类直接调用
//        $res = userModel::where('id', 'eq', $id)->delete();

        //如果想在 模型类文件里 使用 destroy()可以用 parent::destroy()
        $res = userModel::destroy($id);
        if ($res) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }

    //用户中心(展示该用户 发表的博客 和 用户详情)
    public function center()
    {
        //判断 session 中是否有用户信息(要明白为啥要判断,原因为:防止用户在浏览器地址栏输入路径,或者session过了有效期
///       if (Session::has('user')) {
        if (session('?user')) {
            //获取当前用户创建的博客
            $id = input('param.id');

            //博客详情
            $user_sms = model('user_sms')->find($id);
            //该用户详情
            $detail = model('user_sms')->getDetail($id);

            $this->assign(['detail' => $detail, 'blog' => $user_sms->getBlogs()]);
            return $this->fetch();
        } else {
//        没有登陆,跳转到登陆页面
            $this->error('请先登陆', url('index/user/login'));
        };
    }

    //用户退出
    public function logout()
    {
//        //删除用户登陆时,赋值的 session;
//        Session::delete('user.username');
//        //删除用户登陆时,赋值的 id;
//        Session::delete('user.id');

        Session::clear();//清空session数据
        $this->success('退出成功', url('index/user/login'));
    }


}
