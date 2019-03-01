<?php
/**
 * Created by PhpStorm.
 * User: 李秀龙
 * Date: 2018/8/22
 * Time: 16:51
 */

namespace app\common\controller;

use think\Controller;
use think\Request;
use think\Db;

//后台控制器基类
class AdminBase extends Controller
{
    //初始化方法(所有后台控制器里的方法,访问之前都先访问初始化方法)
    public function _initialize()
    {
        //如果管理员已经登陆了,则可以查看
        //如果管理员没有登陆,则跳转到登陆页面
        if (!session('?admin')) {
            $this->error('请先登陆', url('admin/admin/login'));
        }
    }
}