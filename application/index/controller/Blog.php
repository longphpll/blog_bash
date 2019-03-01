<?php

namespace app\index\controller;

use app\common\controller\IndexBase;
use think\Request;
use app\index\model\Blog as BlogModel;

//前台博客控制器
class Blog extends IndexBase
{
    //初始方法
    public function _initialize()
    {
        // 手动调用父类中的初始化方法
        parent::_initialize();

        //通过模型层里的自定义方法 获取最新博客
        $new = model('Blog')->getNew();
        //通过模型层里的自定义方法 获取最热博客
        $view = model('Blog')->getView();

        $jsonObj = $this->check_did(); //返回 \think\response\Json 对象实例
        $arr     = json_decode($jsonObj->getContent(), true);
        if ($arr['code'] != 1) {
            $this->error($arr['msg'], url('index/user/login'));
        }
        return $this->assign(['new' => $new, 'view' => $view]);
    }

    //查询博客列表
    //访问路径:index.php/index/blog/index
    public function index()
    {
        //通过模型层形式
        $list = model('blog')
            ->field('id,uid,title,content,view,created')
            ->order('created DESC')
            ->paginate(3);


        //获取轮播图中的数据
        $slide = model('Blog')->getSlide(4);


        $this->assign(['list'  => $list,
                       'slide' => $slide,
        ]);
        return $this->fetch();
    }

    //添加博客页面
    //访问路径:index.php/index/blog/add
    public function add()
    {
        if (!session('user')) {
            $this->error('请你登陆', url('index/user/login'));
        }
        //调用模板
        return $this->fetch();
    }

    //添加博客的执行方法
    public function doAdd()
    {
        $data = input('post.');
//        print_R($data);die();

        //文件上传
        //1.获取文件数据
        $file = request()->file('image');
        if ($file) {
            $path = ROOT_PATH . 'public/static/upload/';
            $info = $file->validate(['size' => 2048000, 'ext' => 'jpg,png,gif'])->move($path);
            if (is_object($info) && !empty($info->getSaveName())) {
                $data['image'] = $info->getSaveName();
            } else {
                $this->error($file->getError());
            }
        }

        //判断用户是否存在(存在才可添加博客)
        if (session('?user')) {
            $data['uid'] = session('user.id');//当前登陆用户的 id;
            //方式三:通过调用模型层自定义的方法 添加博客
            $res = model('blog')->doAdd($data);
            if ($res) {
                $this->success('添加成功', url('index/blog/add'));
//            $this->redirect(url('index/blog/add'));
            } else {
                $this->error('添加失败', url('index/blog/add'));
            }
        }
    }

    //展示博客详情
    public function view()
    {
//        $id = input('get.id');//获取不到id,因为 get 的取值方式为 ?id..,url上面没有?号
        $id = input('param.id');

        $data = BlogModel::get($id);
//        print_r($data->toArray());//将返回对象转为数组
//        print_r($data->toJson());//将返回对象转为Json字符串

        //查看博客详情时更新博客的浏览量
        //数据库方式
        db('blog')->where('id', 'eq', $id)->setInc('view');

        //查看博客评论
        $this->assign(['data' => $data, 'comments' => $data->comments()]);
        return $this->fetch();
    }

    //删除某条博客
    public function delBlog(Request $request)
    {
//        print_r($request);//think\Request Object(...)
//        $id = $request->param('id');

        //助手函数 request()
//        print_r(request());//think\Request Object(...)
//        $id = request()->param('id');

        //通过调用模型层自定义的方法,实现删除某条博客
        $id  = input('param.id');
        $res = model('blog')->doDel($id);
        if ($res) {
            $this->redirect(url('index/user/center', 'id=' . session('user.id')));
        } else {
            $this->error('删除失败');
        }
    }

}