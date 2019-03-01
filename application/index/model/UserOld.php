<?php

namespace app\index\model;

use think\Model;

//前台的用户模型(tedu_user)
class User extends Model
{
    //模型的时间字段自动写入
    protected $autoWriteTimestamp = true;
    protected $createTime = 'created';
    protected $updateTime = 'false';

    //在添加的场景下自动完成列表
    protected $insert = ['balance' => 50];

    //修改器--对密码明文进行加密
    public function setPasswordAttr($value)
    {
        return md5($value);
    }

    //删除某个用户
    public function doDelete($id)
    {
        return $this->where(['id' => $id])->delete();
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