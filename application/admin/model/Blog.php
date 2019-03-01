<?php
/**
 * Created by PhpStorm.
 * User: 李秀龙
 * Date: 2018/8/22
 * Time: 10:25
 */

namespace app\admin\model;

use think\Model;

//后台博客模型
class Blog extends Model
{
    //时间字段自动完成
    protected $autoWriteTimestamp = true;
    protected $createTime = 'created';
    protected $updateTime = 'updated';

    /**获取博客列表
     * @param int $num 分页记录数
     * @return \think\Paginator 当前模型的对象
     */
    public function getList($where = [], $num = 5)
    {
        //获取搜索参数
        $data = input('param.');

        //按照博客标题搜索
        if (isset($data['title']) && !empty($data['title'])) {
            $where['title'] = ['like', '%' . $data['title'] . '%'];
        }

        //按照作者搜索
        if (isset($data['phone']) && !empty($data['phone'])) {
            //halt($data['phone']); //1802249

            $where1['phone'] = ['like','%'.$data['phone'].'%'];
            $uid = model('User')->field('id')
                ->where($where1)->find();

            $where['uid'] = ['eq', $uid['id']];
        }

        $list = $this->field('id,title,view,created,uid')
            ->where($where)
            ->order('created DESC')
            ->paginate($num);

        return $list;
    }


    //查询博客的作者名称(当前模型是 Blog)
    //查看博客属于哪个用户
    public function author()
    {
        //                     相对关联模型
        return $this->belongsTo('User', 'uid');
    }

    //执行删除博客
    public function doDelete($id)
    {
        return $this->where(['id' => $id])->delete();
    }
}