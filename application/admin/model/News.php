<?php
/**
 * Created by PhpStorm.
 * User: 李秀龙
 * Date: 2018/8/24
 * Time: 11:14
 */

namespace app\admin\model;


use think\Model;

//后台新闻模型
class News extends Model
{
    //模型的时间字段自动写入
    protected $autoWriteTimestamp = true;
    protected $createTime = 'created';
    protected $updateTime = 'updated';

    //新增自动完成列表 的值不能是 函数,可以是数值类型
    protected $insert = [
        'view' => 1,
        'uid',
    ];

    //修改器--自动根据 session 设置字段 uid 的值
    public function setUidAttr()
    {
        return session('admin.id');
    }

    //获取器的命名:get 字段名 Attr
    public function getRecommendAttr($value)
    {
        $status = [
            0 => '<span class="text-danger bg-danger">未推荐</span>',
            1 => '<span class="text-success bg-success">已推荐</span>',
        ];
        return $status[$value];
    }

    //获取器的命名:get 字段名 Attr
    public function getOnlineAttr($value)
    {
        $status = [
            0 => '<span class="text-danger glyphicon  glyphicon-remove" ></span>',
            1 => '<span class="text-success glyphicon glyphicon-ok" ></span>',
        ];
        return $status[$value];
    }

    //新闻列表

    /**
     * @param array $where 查询条件
     * @param int $num 单页记录数
     * @return \think\Paginator
     */
    public function getList($where = [], $num = 5)
    {
        //获取搜索参数
        $cond = input('param.');

        //按照新闻标题搜索
        if (isset($cond['title']) && !empty($cond['title'])) {
            $where['title'] = ['like', '%' . $cond['title'] . '%'];
        }
        //按照是否上线进行搜索
//        if (isset($cond['online']) && is_numeric($cond['online'])) {
//            //online = 1 或者 online = 0
//            $where['online'] = ['eq', $cond['online']];
//        }
        //按照是否推荐进行搜索
        if (isset($cond['recommend']) && is_numeric($cond['recommend'])) {
            //recommend = 1 或者 recommend = 0
            $where['recommend'] = ['eq', $cond['recommend']];
        }

//        print_r($where);
        $list = $this->field('id,title,uid,cid,recommend,online,created')
            ->where($where)
            ->order('CREATED DESC')
            ->paginate($num);
        return $list;
    }

    //查看新闻的所属用户(当前模型是 News)
    public function author()
    {
        //关联失败就报错
        //tedu_news.uid = tedu_user.id
        return $this->belongsTo('Admin', 'uid');
    }

    //查询新闻的所属分类(当前模型是 News)
    public function category()
    {
        //tedu_news.cid = tedu_category.id
        return $this->belongsTo('Category', 'cid');
    }
}