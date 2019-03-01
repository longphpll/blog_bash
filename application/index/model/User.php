<?php

namespace app\index\model;

use think\Model;

//前台 User 模型
class User extends Model
{
    //模型的时间字段自动写入
    protected $autoWriteTimestamp = true;
    protected $createTime = 'created';

//    当新增和更新操作时自动完成 登录时间
    protected $auto = ['log_time'];

    //修改器配合自动完成
    protected function setLogTimeAttr()
    {
        return time();
    }

    //通过 id 查询该用户信息,该方法在 controller/User.php 中调用
    public function getDetail($id)
    {
        return $this->where(['id' => $id])->find();
    }

    //获取某个用户下的博客
    //当前模型时 User
    public function getBlogs()
    {
        //                      关联模型      关联外键
        return $this->hasMany('blog', 'uid')
            ->field('id,uid,title,view,created')
            ->order('created DESC')
            ->paginate(5);
        // SELECT `id`,`uid`,`title`,`view`,`created` FROM `tedu_blog`
        // WHERE `uid` = 2
        // ORDER BY `created`DESC
        // LIMIT 0,5 [ RunTime:0.000434s ]
    }
}
