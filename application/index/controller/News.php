<?php
/**
 * Created by PhpStorm.
 * User: 李秀龙
 * Date: 2018/8/24
 * Time: 21:31
 */

namespace app\index\controller;


use app\common\controller\IndexBase;
use lib\Memcached;


//前台新闻控制器
class News extends IndexBase
{
    //初始方法
    public function _initialize()
    {
        parent::_initialize();

        //通过模型层里的自定义方法 查询最热新闻
        $view = model('News')->getView();

        //通过模型层里的自定义方法 查询最新新闻
        $new = model('News')->getNew();

        $jsonObj = $this->check_did(); //返回 \think\response\Json 对象实例
        $arr = json_decode($jsonObj->getContent(), true);
        if ($arr['code'] != 1) {
            $this->error($arr['msg'], url('index/user/login'));
        }

        $this->assign(['view' => $view, 'new' => $new]);
    }

    //新闻详情页面
    public function view()
    {
        $mc = new memcached(array(
            'servers'            => array('127.0.0.1:11211'),
            'debug'              => false,
            'compress_threshold' => 10240, //10K
            'persistant'         => true));

        $id = input('param.id/d');


        if (is_int($id) && $id > 0) {
            //根据id查看新闻详情
            $data = model('news')->getDetail($id);

            $key = "view" . $id;//view_3
            //首先去内存中拿
            $re = @$mc->get($key);//如果没有返回 null
//            var_dump($re);//209

            if ($re == null) {
                //1.如果没有,浏览量先自增一次
                db('news')->where('id', $id)->setInc('view');
                //2.从表里把浏览量字段查询出来
                $re = db('news')->field('view')->where('id', $id)->find();
                //自增后的浏览量
                $view = $re['view'];

                //把这个值写入到内存中
                $mc->set($key, $view);
            } else {//内存中有
                //内存中的点击数自增1
                $view = $mc->incr($key);
//                var_dump($view);//210
                //每5次同步一次数据库表
                if ($view % 5 == 0) {  //8+1+1 +1+1+1+1+1
                    //更新某个字段用 setFiled();
                    db('news')->where('id', $id)->setField('view', $view);
                }
            }

            $this->assign(['data' => $data, 'comments' => $data->comments(), 'view' => $view]);

            return $this->fetch();

        } else {
            $this->error('参数非法');
        }
    }
}