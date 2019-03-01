<?php
/**
 * Created by PhpStorm.
 * User: 李秀龙
 * Date: 2018/8/24
 * Time: 21:34
 */

namespace app\index\model;


use think\Model;

//前台新闻模型
class News extends Model
{
    //模型的时间字段自动写入
    protected $autoWriteTimestamp = true;
    protected $createTime = 'created';
    protected $updateTime = 'updated';

    //新闻详情
    public function getDetail($id)
    {
        $data = $this->field('id,uid,cid,title,content,image,view,created')
            ->find($id);
        return $data;
    }

    //获取新闻的所属用户(当前模型是 News)
    public function author()
    {
        //关联失败就报错
        //tedu_user.id=tedu_news.uid
        return $this->belongsTo('Admin', 'uid');
    }

    //获取新闻的所属分类(当前模型是 News)
    public function category()
    {
        //tedu_category.id=tedu_news.cid
        return $this->belongsTo('Category', 'cid');
    }

    //获取 最热新闻
    public function getView($num = 5)
    {
        $data = $this->field('id,title,view')
            ->where('online=1')
            ->order('view DESC')
            ->limit($num)
            ->select();
        return $data;
    }

    //获取 最新新闻
    public function getNew($num = 5)
    {
        $data = $this->field('id,title')
            ->where('online=1')
            ->order('created DESC')
            ->limit($num)
            ->select();
        return $data;
    }

    /**根据分类id数组,查询轮播图
     * @param array|null $cids 分类id数组
     * @param int $num 查询记录数
     * @return array
     */
    public function getSlide($cids = null, $num = 5)
    {
        if (is_null($cids)) {
            return $this->field('id,title,image')
                ->where('image', 'neq', '')
                ->order('created DESC')
                ->limit($num)
                ->select();
        } else if (is_array($cids)) {
            return $this->field('id,title,image')
                ->where('cid', 'in', $cids)
                ->where('image', 'neq', '')
                ->where('online=1')
                ->order('created DESC')
                ->limit($num)
                ->select();
        }


    }

    //根据某分类id(包含后代分类)数组,查询推荐新闻

    /**
     * @param array|null $cids 分类的id
     * @param int $num 返回结果的数量
     */
    public function getRecommend($cids = null, $num = 10)
    {
        if (is_null($cids)) {
            return $this->field('id,title')
                ->where(['online' => 1, 'recommend' => 1])
                ->order('created DESC')
                ->limit($num)
                ->select();
        } else if (is_array($cids)) {
            return $this->field('id,title')
                ->where('cid', 'in', $cids)
                ->where(['online' => 1, 'recommend' => 1])
                ->order('created DESC')
                ->limit($num)
                ->select();
        }
    }

    /**
     * 根据分类id数组,查询最新的10条新闻
     *
     * @param  array $cids 分类id数组
     * @param  integer $num 查询数量
     * @return array
     */
    public function getNewsByCids($cids, $num = 10)
    {
        return $this->field('id,title')
            ->where('cid', 'in', $cids)
            ->where('online=1')
            ->order('created DESC')
            ->limit($num)
            ->select();
    }

    //获取新闻下的评论(当前模型是 News)
    //原生SQL: comment_id=当前新闻id AND comment_type='News'
    public function comments()
    {
        //多态一对多
        return $this->morphMany('Comment', ['comment_type', 'comment_id'], $this->name)
            ->order('created DESC')
            ->paginate(5);
    }
}