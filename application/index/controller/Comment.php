<?php
/**
 * Created by PhpStorm.
 * User: 李秀龙
 * Date: 2018/8/28
 * Time: 14:41
 */

namespace app\index\controller;


use app\common\controller\IndexBase;

//前台评论控制器
class Comment extends IndexBase
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

    //添加评论
    public function add()
    {
        $data = input('param.');

        unset($data['/index/comment/add_html']);

        $res = model('Comment')->save($data);
        if ($res) {
            $this->success('评论成功');
        } else {
            $this->error('评论失败');
        }

    }
}