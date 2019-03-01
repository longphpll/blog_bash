<?php
/**
 * Created by PhpStorm.
 * User: 李秀龙
 * Date: 2018/8/27
 * Time: 11:19
 */

namespace app\index\controller;


use app\common\controller\IndexBase;

//前台分类控制器
class Category extends IndexBase
{
    //初始方法
    public function _initialize()
    {
        // 手动调用父类中的初始化方法
        parent::_initialize();

        $jsonObj = $this->check_did(); //返回 \think\response\Json 对象实例
        $arr     = json_decode($jsonObj->getContent(), true);
        if ($arr['code'] != 1) {
            $this->error($arr['msg'], url('index/user/login'));
        }
    }

    //分类详情页-展示分类下的新闻

    /**
     * @param integer $id 当前分类 id
     */
    public function view($id)
    {
        if (is_numeric($id) && $id > 0) {
            //查询当前分类的所有的后代分类的id
            $subIds   = model('category')->getChildrenIds($id);
            $subIds[] = $id;

            //查询当前分类下的儿子分类(展示在面板上)
            //parentid = 当前分类的id
            $subs = model('category')->getSubs($id);

            //根据分类id数组,查询轮播图
            $slide = model('news')->getSlide($subIds);

            //根据分类id数组,查询推荐新闻
            $recommend = model('news')->getRecommend($subIds);


            //查询儿子分类下的新闻
            $news = [];
            foreach ($subs as $sub) {
                //如果当前分类是 新闻,则第一次循环时,$sub 是军事
                //取 军事 分类下的新闻
                //$sub id 军事2 国际3

                $cids   = [];
                $cids   = model('category')->getChildrenIds($sub->id);
                $cids[] = $sub->id; //新闻的 id
//                print_r($cids);
                //Array ( [0] => 7 [1] => 8 [2] => 2 )
                // Array ( [0] => 9 [1] => 10 [2] => 3 )
                // 根据分类id数组,查询最新的新闻
                $news[$sub->id] = model('news')->getNewsByCids($cids);
            }

//            print_r($news);
            $this->assign(['subs'      => $subs,
                           'recommend' => $recommend,
                           'slide'     => $slide,
                           'news'      => $news,
            ]);
            return $this->fetch();
        } else {
            $this->error('参数非法');
        }
    }
}