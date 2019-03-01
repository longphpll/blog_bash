<?php
/**
 * Created by PhpStorm.
 * User: 李秀龙
 * Date: 2018/8/24
 * Time: 10:25
 */

namespace app\admin\controller;

use app\common\controller\AdminBase;
use think\Request;
use app\admin\controller\Blog;

//后台新闻控制器
class News extends AdminBase
{
    //新闻列表
    public function index()
    {
        $list = model('news')->getList();

        $this->assign('list', $list);
        return $this->fetch();
    }

    //添加新闻 展示页面
//变量名绑定不一定由访问URL决定，路由地址也能起到相同的作用
//方法 参数绑定 是把URL地址（或者路由地址）中的变量作为操作方法的参数直接传入
//对于上面的话要理解，即 参数绑定是把 URL地址 或者 路由地址。。。。

//URL地址 //http://blog/public/newadd/id/1/year/2019.pdff
//路由地址 //http://blog/public/newadd/1/2019.pdff
    public function add($id = '', $year = '')
    {
        //获取新闻分类树形结构
        $cs = model('category')->getTree();

        $this->assign('cs', $cs);
        return $this->fetch();
    }

    //添加新闻 执行方法
//    public function doAdd(Request $request)
    public function doAdd()
    {

        //排除 cid 请求参数
//        $res = $request->except(['cid']);
//         print_r($res);

        //请求参数 仅包含 cid
//        $res = $request->only('cid');
//        print_r($res);

//        print_r($request->dispatch());


        $data = input('param.');
        //文件上传
        //1.获取文件数据
        $file = request()->file('image');
        if ($file) {
            //2.执行上传
            $path = ROOT_PATH . 'public/static/upload/';
            $info = $file->validate([
                'size' => 2048000,//单位:字节
                'ext'  => 'jpg,jpeg,png,gif'
            ])->move($path);
            if (is_object($info) && !empty($info->getSaveName())) {
                $data['image'] = $info->getSaveName();
            } else {
                $this->error($file->getError());
            }
        }

        //判断是否上线
        if (!isset($data['online'])) {
            $data['online'] = 0;
        }

        $res = model('news')->save($data);
        if ($res) {
            $this->success('添加成功');
        } else {
            $this->error('添加失败');
        }
    }

    //新闻编辑 页面
    public function edit()
    {
        $id = input('param.id/d');
        if (is_int($id) && $id > 0) {
            //获取新闻分类树形结构
            $cs   = model('category')->getTree();
            $data = model('news')->find($id);

            $this->assign(['data' => $data, 'cs' => $cs]);
            return $this->fetch();
        } else {
            $this->error('参数非法');
        }
    }

    //新闻编辑 执行方法
    public function doEdit()
    {
        $data = input('post.');
        $id   = input('post.id/d');
        if (is_int($id) && $id > 0) {
            //判断是否推荐
            if (!isset($data['recommend'])) {
                $data['recommend'] = 0;//不推荐
            }
            //判断是否上线
            if (!isset($data['online'])) {
                $data['online'] = 0;//下线
            }

            //文件上传
            //1.获取文件数据
            $file = request()->file('image');
            if ($file) {
                //2.执行上传
                $path = ROOT_PATH . 'public/static/upload/';
                $info = $file->validate([
                    'size' => 2048000,//单位:字节
                    'ext'  => 'jpg,png,gif'
                ])->move($path);
                if (is_object($info) && !empty($info->getSaveName())) {
                    $data['image'] = $info->getSaveName();
                } else {
                    $this->error($file->getError());
                }
            }

            //进行保存
            unset($data['id']);
            $res = model('news')
                ->save($data, ['id' => $id]);

//            $res = Db::name('news')->getLastSql();
//            echo $res;
//            die();

            if ($res) {
                $this->success('编辑新闻成功', url('admin/news/index'));
            } else {
                $this->error('编辑新闻失败');
            }
        } else {
            $this->error('参数非法');
        }
    }

    //删除新闻 执行方法
    public function delete()
    {
        $id = input('param.id/d');
        if (is_int($id) && $id > 0) {
            $res = model('news')->where(['id' => $id])->delete();
            if ($res) {
                $this->success('删除成功');
            } else {
                $this->error('删除失败');
            }
        } else {
            $this->error('参数非法');
        }
    }

}