<?php
/**
 * Created by PhpStorm.
 * User: 李秀龙
 * Date: 2018/8/27
 * Time: 10:36
 */

namespace app\common\controller;

use think\Controller;

use think\cache\driver\Redis;

//前台基础控制器
//前台所有的控制器都需要查询推荐分类菜单,声明前台的基础控制器 IndexBase
class IndexBase extends Controller
{
    protected $redis; //将来子类控制器里可以直接访问这个$redis对象

    //初始化方法
    public function _initialize()
    {
        //接收前端数据
        $this->datas = input();

        //测试使用, 正式使用时注释这行代码
        $did         = [
            'program_name' => 'front_web_user',
            'env_string'   => '00：01：6C：06：A6：29',
            'internet_ip'  => '192.168.80.7',
            'DID'          => 'TRF_20190227212017_PsJa3Qc97nJsW',
        ];
        $this->datas = array_merge($this->datas, $did);

        //实例化 redis 对象
        $this->redis = Redis::getInstance();

        //查询推荐的分类菜单
        $cmenu = model('category')->getNav();
        $this->assign('cmenu', $cmenu);
    }

    /**
     * 验证DID
     * 所有操作进行前都进行验证DID
     * 分析DID步骤
     * 1.用户的所有操作前都要调用
     * 2.查询Redis缓存中KEY为DID的得到Value $user_info,如果为空说明 DID已过期
     * 3.根据 $user_info['cellphone'] 查询 Redis,得到用户之前的 DID，如果当前DID和用户之前的DID一致,验证通过,
     * 如果不一致,说明异地登录,返回登录页
     *
     */
    public function check_did()
    {
        // 通过controller里的方法 validate() 验证did
        $validateRes = $this->validate($this->datas, 'BaseValidate.checkDid');
        if ($validateRes !== true) return error($validateRes);

        //Redis作用
        //1、实现带有效期的登录状态保持(即did是否过期,过期了提示用户重新登录)
        //2、实现用户的唯一设备登录(即防止用户异地登录);

        // 查询 Redis(由于用户在登录成功后时在redis里 set 了以did为key的键,expire为30分钟)
        $user_info = $this->redis->get('TRF_DID_' . $this->datas['DID']);


        //如果为false,说明did已过期,提示用户重新登录
        if (!$user_info) return error('DID已过期,请重新登录');
        //如果有说明登录状态没过期,并且在30分钟内用户不断的在操作页面,这时就要延长 did 有效时间
        //刷新 Redis
        $this->redis->set('TRF_DID_' . $this->datas['DID'], $user_info);


        $last_did = $this->redis->get('TRF_PHONE_' . $user_info['username']);//TRF_20190227093023_yAidMhbSnmUEV
        //如果用户在另一台设备B登录那么就会产生新的did,然后返给前端,那么用户在设备A再次浏览页面时DID就会是新的DID,
        //那么就和之前登录时存的DID不一样,进而可以判断是否为异地登录
        //如果从服务器上传过来的did(这个是动态的) !== $last_did() 说明为异地登录
        if ($this->datas['DID'] !== $last_did) {
            return error('手机号为' . $user_info['username'] . '的用户异地登录!');
        }
        //如果不是异地登录同样延长 did 有效时间
        //刷新 Redis
        $this->redis->set('TRF_PHONE_' . $user_info['username'], $this->datas['DID']);


        return success('过期时间刷新成功!');
        //{"code":1,"msg":"过期时间刷新成功!"}
    }

}