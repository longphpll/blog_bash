<?php
/**
 * Created by PhpStorm.
 * User: 李秀龙
 * Date: 2018/8/22
 * Time: 10:19
 */

namespace app\admin\controller;

use app\common\controller\AdminBase;

//后台 Blog 控制器
class Blog extends AdminBase
{
    //可以动态注入当前Request对象的属性
    public $bindProperty = 'user';
    public $username  = 'tom';

    //博客列表
    public function index()
    {
        //通过模型层自定义的方法 查询博客列表
        $list = model('blog')->getList();

        $this->assign(['list' => $list]);
        return $this->fetch();
    }

    //删除某一条博客
    public function delete()
    {
        //接收参数
        $id = input('param.id/d');

        //方式一:通过数据层 删除某条博客
        $res = db('blog')->delete($id);

        //方式二:通过模型层自定义的方法 删除某条博客
//        $res = model('blog')->doDelete($id);

        if ($res) {
            $this->success('删除成功');
        } else {
            $this->error('删除成功');
        }
    }

    //编辑博客
    public function edit($id)
    {
        if (is_numeric($id) && $id > 0) {
            $data = model('blog')->find($id);
            $this->assign('data', $data);
            return $this->fetch();
        } else {
            $this->error('参数非法');
        }
    }

    //编辑博客 执行方法
    public function doedit()
    {
        $data = input('param.');
        $id   = input('param.id/d');
        if (is_int($id) && $id > 0) {
            $file = request()->file('image');
            if ($file) {
                $path = ROOT_PATH . '/public/static/upload/';
                $info = $file->validate(['size' => 2048000, 'ext' => 'gif,jpg,png'])
                    ->move($path);
                if (is_object($info) && !empty($info->getSaveName())) {
                    $data['image'] = $info->getSaveName();
                } else {
                    $this->error($file->getError());
                }
            }

            //进行保存
            $res = model('blog')->save($data, ['id' => $id]);
            if ($res) {
                $this->success('编辑博客成功', url('admin/blog/index'));
            } else {
                $this->error('编辑博客失败');
            }
        } else {
            $this->error('参数非法');
        }
    }
}

