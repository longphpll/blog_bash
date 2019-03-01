<?php
/**
 * Created by PhpStorm.
 * User: 李秀龙
 * Date: 2018/8/28
 * Time: 20:23
 */

namespace app\admin\controller;

use app\common\controller\AdminBase;

//后台评论控制器
class Comment extends AdminBase
{
    //评论列表
    /**
     * @return void
     */
    public function index()
    {
        //查询评论列表
        $list = model('Comment')->getList();
//        halt($list);
        $this->assign('list', $list);
        return $this->fetch();
    }
    
    //删除评论
    public function delete($id)
    {
       if(is_numeric($id) && $id>0){
           $res = model('comment')->where('id','eq',$id)->delete() ;
           if($res){
               $this->success('删除成功');
           }else{
               $this->error('删除失败');
           }
       }else{
           $this->error('参数非法');
       }
    }
}