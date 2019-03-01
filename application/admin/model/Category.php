<?php
/**
 * Created by PhpStorm.
 * User: 李秀龙
 * Date: 2018/8/23
 * Time: 10:56
 */

namespace app\admin\model;


use think\Model;

//后台分类模型
class Category extends Model
{
    protected $autoWriteTimestamp = true;
    protected $createTime = 'created';
    protected $updateTime = false;

    //获取器的命名:get 字段名 Attr
    public function getRecommendAttr($value, $data)
    {
        $status = [
            0 => '<span class="text-danger">未推荐</span>',
            1 => '<span class="text-success">已推荐</span>',
        ];
        return $status[$value];
    }

    //获取分类的树形结构

    /**
     * @param int $parent 父id
     * @param arr $target
     */
    public function getTree($parentid = 0, $target = [])
    {
        $cs = $this->field('id,title,recommend,created')
            ->where(['parentid' => $parentid])->select();

        //默认查询一级分类
        // $cs = [体育,娱乐]
        static $n = 1;

        foreach ($cs as $c) {
            //第一次遍历 $c = 体育
            //$c 是 category 模型的对象
//            print_r($c->id);
//            exit;

            $tmp          = null;
            $tmp          = $c->toArray();
            $tmp['level'] = $n;

            $target[] = $tmp;
            //体育的id是: $c->id;

            $n++;//进入递归之前,分类级别+1
            $target = $this->getTree($c->id, $target);
            $n--;//跳出递归之后,分类级别-1
            //第二次遍历 $c = 娱乐
        }
//        print_r($target);
        return $target;
    }

}