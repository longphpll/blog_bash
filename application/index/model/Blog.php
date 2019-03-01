<?php

namespace app\index\model;

use think\Model;

/**
 * 前台博客模型(tedu_blog)
 */
class Blog extends Model
{
    //模型的时间字段自动写入
    protected $autoWriteTimestamp = true;
    protected $createTime = 'created';
    protected $updateTime = 'updated';

    //完成添加博客
    public function doAdd($data)
    {
        return $this->data($data)->save();
    }

    //获取最新博客
    public function getNew($num = 5)
    {
        return $this->field('id,title')
            ->order('created DESC')
            ->limit($num)
            ->select();
    }

    //获取最热博客
    public function getView($num = 5)
    {
        return $this->field('id,title,view')
            ->order('view DESC')
            ->limit($num)
            ->select();
    }

    //通过 id 查询该用户发表的博客 该方法在 controller/User.php 中调用
    public function getBlog($id)
    {
        return $this->where(['uid' => $id])
            ->order('created DESC')
            ->select();
    }

    //通过 id 删除某条博客 该方法在 controller/Blog.php 中调用
    public function doDel($id)
    {
        //模型层里的方法
//        return parent::get($id)->delete();//如果再次刷新会报错//致命错误: Call to a member function delete() on null
//        return parent::destroy($id);
        return self::destroy($id);

        //数据层里的方法
//        return $this->where(['id' => $id])
//            ->delete();
    }

    //查询某篇博客的作者
    //当前的模型是 Blog
    public function author()
    {
        //相对关联模型
        return $this->belongsTo('user', 'uid');
    }

    //获取轮播图中展示的数据
    public function getSlide($num = 3)
    {
//        print_R($this->author());
        return $this->field('id,title,image')
            ->where('image', 'neq', '')
            ->order('created DESC')
            ->limit($num)
            ->select();
    }

    //获取博客下的评论(当前模型是 Blog)
    public function comments()
    {
        //多态一对多
        //原生SQL: comment_id=当前博客id AND comment_type='Blog'
//        return $this->morphMany('Comment','comment');//这样写得不到结果
        return $this->morphMany('Comment', ['comment_type', 'comment_id'], $this->name)
            ->order('created DESC')
            ->paginate(5);
    }
}