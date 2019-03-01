<?php
/**
 * Created by PhpStorm.
 * User: 李秀龙
 * Date: 2018/8/23
 * Time: 10:55
 */

namespace app\admin\controller;

use app\common\controller\AdminBase;


//后台分类控制器
class Category extends AdminBase
{
    //初始化方法(把树形结构的查询,放到了分类控制器的初始化当中)
    public function _initialize()
    {
        //方法重名后,默认只调用子类中的同名方法
        //如果在子类中调用父类中的同名方法,
        //需要使用 parent 关键字
        parent::_initialize();
        //获取分类树形结构
        $cs = model('category')->getTree();
        $this->assign('cs', $cs);
    }

    //分类列表
    public function index()
    {
        return $this->fetch();
    }

    //添加分类
    public function add()
    {
        return $this->fetch();
    }

    //添加分类 的执行方法
    public function doAdd()
    {
        $data = input('param.');
//        print_r($data);
//        exit;
        $res = model('category')->save($data);
        if ($res) {
            $this->success('添加成功');
        } else {
            $this->error('添加失败');
        }
    }

    //编辑页面
    public function edit()
    {
        $id = input('param.id/d');

        if (is_int($id) && $id > 0) {
            $data = model('category')->find($id);
            $this->assign('data', $data);
            return $this->fetch();
        } else {
            $this->error('参数非法');
        }
    }

    //编辑页面 的执行方法
    public function doEdit()
    {
        $id   = input('param.id/d');
        $data = input('post.');
        if (is_int($id) && $id > 0) {
            if (!isset($data['recommend'])) {
                $data['recommend'] = 0;//不推荐
            }
            //进行保存
            $res = model('category')->save($data, ['id' => $id]);
            if ($res) {
                $this->redirect(url('admin/category/index'));
            } else {
                $this->error('保存失败');
            }
        } else {
            $this->error('参数非法');
        }
    }
}
