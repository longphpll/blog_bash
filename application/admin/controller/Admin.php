<?php
/**
 * Created by PhpStorm.
 * User: 李秀龙
 * Date: 2018/8/22
 * Time: 14:05
 */

namespace app\admin\controller;

use think\Controller;
use think\Exception;
use think\Session;
use app\admin\model\Admin as adminModel;

//后台用户控制器   //它不能继承基类
class Admin extends Controller
{
    /**
     * 前置操作: 在访问某些方法之前,优先需要访问的内容
     * 访问 Admin 控制器的方法之前,优先访问 checkAdmin 方法;
     * 但是 login() 方法和 doLogin() 方法除外
     * except 排除    only   只包含
     * 注意: 前置操作中的排除或包含的方法名,要写成小写
     */
    protected $beforeActionList = [
        'checkAdmin' => ['except' => 'login,dologin'],
        //除了 login,和 delogin 方法外,其它都执行 checkAdmin() 方法
    ];

    //后台 登陆页面
    //访问路径:admin/admin/login
    public function login()
    {
        return $this->fetch();
    }

    //后台 完成登陆 执行方法
    public function doLogin()
    {

        $data = input('param.');
        //验证 验证码的有效性
//        if (!captcha_check($data['captcha'])) {
//            $this->error('验证码输入不正确');
//        }

        $admin = db('Admin')->field('id,username')
            ->where(['username' => $data['username'], 'pwd' => md5($data['pwd'])])
//                ->fetchSql()
            ->find();
//        halt($admin);

        if (!empty($admin)) {
//            将模型层查询出的结果(对象)转为数组,存入 session 中
//            $admin = $admin->toArray();

            //登陆成功,将用户信息赋值给 session
            session('admin', $admin);
            $this->redirect(url('admin/index/index'));
        } else {
            $this->error('登陆失败');
        }
    }

    //添加管理员
    public function register()
    {
        return $this->fetch();
    }

    //执行 添加管理员
    public function doRegister()
    {
        $data = input();

        $validate = new \app\admin\validate\Admin;
        if (!$validate->check($data)) {
            return $this->error($validate->getError());
        }

        $res = adminModel::create($data, true);
        if ($res) {
            //添加成功
            $this->success('用户注册成功');
        } else {
            //添加失败
            $this->error($res->getError());
        }
    }

    //管理员退出
    public function logout()
    {
        //删除管理员的 session
        Session::delete('admin');//方式一
        session('admin', null);//方式二

        //调转到登陆界面
        $this->redirect(url('admin/admin/login'));
    }

    //用户列表
    public function index()
    {
        $list = model('admin')->getList();

        $this->assign('list', $list);
        return $this->fetch();
    }

    //删除用户
    public function delete()
    {
        $id = input('param.id');

        $res = db('admin')->delete($id);

        if ($res) {
            $this->redirect(url('admin/admin/index'));
        } else {
            $this->error('删除失败');
        }
    }

    //编辑用户
    public function edit()
    {
        $id = input('param.id');

        $detail = model('admin')->getDetail($id);

        $this->assign('detail', $detail);
        return $this->fetch();
    }

    //完成编辑用户
    public function doEdit($id)
    {
        if (is_numeric($id) && $id > 0) {
            $data = input('post.');
            if (!isset($data['admin'])) {
                $data['admin'] = 0;
            }

            if ($data['pwd'] != $data['repwd']) {
                $this->error('两次密码不一致');
            }

            $res = model('admin')->doEditt($data, $id);

            if ($res) {
                $this->redirect('admin/admin/index');
            } else {
                $this->error('编辑失败');
            }
        } else {
            $this->error('参数非法');
        }
    }

    //检查管理员的登陆状态
    public function checkAdmin()
    {
        if (!session('?admin')) {
            $this->redirect(url('admin/admin/login'));
        }
    }

}