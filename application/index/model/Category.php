<?php
/**
 * Created by PhpStorm.
 * User: 李秀龙
 * Date: 2018/8/24
 * Time: 22:06
 */

namespace app\index\model;


use think\Model;

//前台分类模型
class Category extends Model
{
    //模型的时间字段自动写入
    protected $autoWriteTimestamp = true;
    protected $createTime = 'created';
    protected $updateTime = false;

    //查询推荐的分类(导航栏的分类)
    public function getNav()
    {
        return $this->field('id,title')
            ->where('recommend=1')
            ->select();
    }

    //查询某分类的儿子分类(二级)
    //传入的 $id 参数 为父id
    public function getSubs($id)
    {
        return $this->field('id,title')
            ->where('parentid', 'eq', $id)
            ->select();
    }

    //获取一级分类
    public function getSub()
    {
        return $this->field('id,title')
            ->where('parentid', 'eq', 0)
            ->select();
    }

    //根据某个分类的 id,查询其所有后代分类的 id
    //传入的 $id 参数 为父id
    public function getChildrenIds($id, $arr = [])
    {
        //假定当前 $id=新闻的id; //ID=1
        //查询新闻的儿子分类(军事,国际)
        $cs = $this->field('id,title')
            ->where(['parentid' => $id])
            ->select();

        //$cs = [军事,国际]
        //通过 foreach 循环,依次获取 军事,国际下的 子id
        foreach ($cs as $c) {
            //第一次遍历的是军事
            $arr[] = $c->id;
            $arr   = $this->getChildrenIds($c->id, $arr);
            //第二次遍历的是国际
        }
        return $arr;
    }
}