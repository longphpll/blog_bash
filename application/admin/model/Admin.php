<?php
/**
 * Created by PhpStorm.
 * Admin: 李秀龙
 * Date: 2018/8/22
 * Time: 10:59
 */

namespace app\admin\model;

use think\Model;

//后台用户模型
class Admin extends Model
{
    //模型的时间字段自动写入
    protected $autoWriteTimestamp = true;
    protected $createTime = 'created';
    protected $updateTime = false;

    //修改器
    public function setPwdAttr($value)
    {
        return md5($value);
    }

    //获取器
    public function getAdminAttr($value)
    {
        $status = [
            0 => '<span class="text-danger">不是</span>',
            1 => '<span class="text-success">是</span>'
        ];
        return $status[$value];
    }

//    public function doLogin($data)
//    {
//由于 allowField(true) 一般在添加数据和更新数据时使用,且和模型层里的添加方法配合使用,不能和数据层里的方法配合使用,
//查询时 find() 不能使用,所以在控制层传参之前将 数组 $data 下的 captcha 键位删除  unset($data['captcha']);
//        return $this->field('id,username')
//            ->where($data)->where(['admin' => 1])
//            ->find();
//    }

//查询用户列表
    public function getList($num = 5)
    {
        return $this->field('id,username,email,phone,balance,created,admin')
            ->order('created DESC')
            ->paginate($num);
    }

//查询某个用户详情
    public function getDetail($id)
    {
        return $this->find($id);
    }

//完成编辑
    public function doEditt($data, $id)
    {
//allowField(true) 一般在添加数据和更新数据时使用,且和模型层里的添加方法配合使用,
//不能和数据层里的添加方法配合使用  查询数据是不能使用 allowField(true) 的
        return $this->allowField(true)->save($data, ['id' => $id]);
    }
}