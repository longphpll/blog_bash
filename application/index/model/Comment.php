<?php
/**
 * Created by PhpStorm.
 * User: 李秀龙
 * Date: 2018/8/28
 * Time: 11:51
 */

namespace app\index\model;


use think\Model;

//前台评论模型
class Comment extends Model
{
    protected $autoWriteTimestamp = true;
    protected $createTime = 'created';
    protected $updateTime = false;

    //查看评论的作者信息
    public function author()
    {
        return $this->belongsTo('User','uid');
    }
}